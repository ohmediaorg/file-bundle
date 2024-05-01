<?php

namespace OHMedia\FileBundle\Service;

use Doctrine\DBAL\Connection;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Util\ImageResource;
use OHMedia\FileBundle\Util\MimeTypeUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileLifecycle
{
    private FileSystem $fileSystem;
    private AsciiSlugger $slugger;

    public function __construct(
        private Connection $connection,
        private FileRepository $fileRepository,
        private FileManager $fileManager
    ) {
        $this->fileSystem = new FileSystem();
        $this->slugger = new AsciiSlugger();
    }

    public function prePersist(FileEntity $file)
    {
        if ($file->isCloned()) {
            $copy = $this->fileManager->copy($file);

            if (!$copy) {
                // EDGE CASE: setFile(null) was called then the file was cloned
                // everything will be null except for $oldPath
                // we want to set $oldPath to null so the file this was cloned
                // from is not deleted
                $file->setOldPath(null);

                return;
            }

            $this->preSaveFile($copy);

            // transfer important values back to $file
            $file
                // IMPORTANT: resetting path prior to calling setFile()
                ->setPath(null)
                ->setFile($copy->getFile())
                ->setPath($copy->getPath())
                ->setToken($copy->getToken())
            ;
        } else {
            $this->preSaveFile($file);
        }
    }

    public function postPersist(FileEntity $file)
    {
        $this->postSaveFile($file);
    }

    public function preUpdate(FileEntity $file)
    {
        $this->preSaveFile($file);
    }

    public function postUpdate(FileEntity $file)
    {
        $this->postSaveFile($file);
    }

    public function postRemove(FileEntity $file)
    {
        $this->removeFilepath($file->getPath());
    }

    private array $newFiles = [];

    private function preSaveFile(FileEntity $file)
    {
        $httpFile = $file->getFile();

        if (null === $httpFile) {
            return;
        }

        $token = $this->generateFileToken();

        $file->setToken($token);

        if (!$file->getResizeParent()) {
            $this->setFileDimensions($file, $httpFile);
        }

        if ($httpFile instanceof UploadedFile) {
            $name = $httpFile->getClientOriginalName();
            $ext = $httpFile->getClientOriginalExtension();

            $name = preg_replace('/\.'.preg_quote($ext).'$/', '', $name);

            $name = $this->slugger->slug($name);
            $ext = $this->slugger->slug($ext);

            $file
                ->setName($name)
                ->setExt($ext)
            ;
        }

        $ext = $httpFile->guessExtension();

        if ($ext) {
            $ext = '.'.$ext;
        }

        $now = new \DateTime();

        $basename = $now->format('Hisu');

        $path = $now->format('Y/m/d');

        $fullPath = $this->fileManager->getAbsoluteUploadDir().'/'.$path;

        $this->fileSystem->mkdir($fullPath);

        $i = 1;
        $temp = $basename;
        while (glob("$fullPath/$temp.*") || isset($this->newFiles["$fullPath/$temp"])) {
            $temp = $basename.'-'.$i;

            ++$i;
        }

        $this->newFiles["$fullPath/$temp"] = 1;

        $path .= '/'.$temp.$ext;

        $mimeType = $this->fileManager->getMimeType($file);

        $size = $httpFile->getSize();

        $file
            ->setPath($path)
            ->setMimeType($mimeType)
        ;
    }

    private function generateFileToken(): string
    {
        $lowercase = implode('', range('a', 'z'));
        $numbers = implode('', range(0, 9));

        $chars = $lowercase.$numbers;
        $lastIndex = strlen($chars) - 1;

        $length = FileEntity::TOKEN_LENGTH;

        do {
            $token = '';

            for ($i = 0; $i < $length; ++$i) {
                $token .= $chars[rand(0, $lastIndex)];
            }
        } while ($this->fileRepository->findOneByToken($token));

        return $token;
    }

    private function setFileDimensions(FileEntity $file, HttpFile $httpFile)
    {
        $width = $height = null;

        $imageSize = @getimagesize($httpFile->getRealPath());

        if ($imageSize) {
            $width = $imageSize[0];
            $height = $imageSize[1];
        }

        $file
            ->setWidth($width)
            ->setHeight($height)
        ;
    }

    private function postSaveFile(FileEntity $file)
    {
        $filepath = $file->getPath();

        if ($filepath && $file->getFile()) {
            if (!$this->fileManager->isValidUploadFilepath($filepath)) {
                throw new \Exception('Invalid filepath');
            }

            $path = explode('/', $filepath);
            $name = array_pop($path);
            $path = implode('/', $path);

            $uploadDir = $this->fileManager->getAbsoluteUploadDir();

            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $file->getFile()->move($uploadDir.'/'.$path, $name);

            $this->doImageProcessing($file);
        }

        // check if we have an old file
        if ($file->getOldPath()) {
            // delete the old file
            $this->removeFilepath($file->getOldPath());
            // clear the temp file path
            $file->setOldPath(null);
        }

        $file->clearFile();

        $this->postSaveResize($file);

        $filepath = $this->fileManager->getAbsolutePath($file);

        // make sure the file size is accurate post save using a raw query
        // to avoid triggering more Doctrine life-cycles
        if (file_exists($filepath)) {
            $filesize = filesize($filepath);

            $stmt = $this->connection->prepare('
                UPDATE `file`
                SET `size` = :size
                WHERE `id` = :id'
            );

            $stmt->bindValue('size', $filesize);
            $stmt->bindValue('id', $file->getId());

            $stmt->executeQuery();
        }
    }

    private function doImageProcessing(FileEntity $file)
    {
        $mimeType = $file->getMimeType();

        if ($mimeType && !MimeTypeUtil::isResizeEligible($mimeType)) {
            return;
        }

        $filepath = $this->fileManager->getAbsolutePath($file);

        $imageResource = ImageResource::create($filepath);

        if (!$imageResource) {
            return;
        }

        $imageResource->fixOrientation()->save();
    }

    private function postSaveResize(FileEntity $file)
    {
        $mimeType = $file->getMimeType();

        if ($mimeType && !MimeTypeUtil::isResizeEligible($mimeType)) {
            return;
        }

        if (!$file->getResizeParent()) {
            return;
        }

        $filepath = $this->fileManager->getAbsolutePath($file);

        $imageResource = ImageResource::create($filepath);

        if (!$imageResource) {
            return;
        }

        $width = $file->getWidth();
        $height = $file->getHeight();

        $imageResource->resize($width, $height);

        $imageResource->save($filepath);
    }

    private function removeFilepath(?string $filepath)
    {
        if (!$this->fileManager->isValidUploadFilepath($filepath)) {
            return;
        }

        $uploadDir = $this->fileManager->getAbsoluteUploadDir();

        $absolutePath = $uploadDir.'/'.$filepath;

        // Only delete files that are not symlinks.
        // Again, this should never happen, but better safe than sorry!
        if (is_file($absolutePath) && !is_link($absolutePath)) {
            $this->fileSystem->remove($absolutePath);
        }
    }
}
