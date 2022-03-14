<?php

namespace OHMedia\FileBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Repository\ImageRepository;
use OHMedia\FileBundle\Util\ImageResource;
use OHMedia\FileBundle\Util\ImageUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileManager
{
    const FILE_DIR = 'oh_media_files';

    private $absoluteUploadDir;
    private $em;
    private $fileRepo;
    private $imageRepo;
    private $fileSystem;
    private $router;
    private $slugger;

    public function __construct(
        EntityManager $em,
        UrlGeneratorInterface $router,
        string $projectDir
    )
    {
        $this->absoluteUploadDir = $projectDir . '/' . static::FILE_DIR;
        $this->em = $em;
        $this->fileRepo = $em->getRepository(FileEntity::class);
        $this->fileSystem = new FileSystem();
        $this->imageRepo = $em->getRepository(Image::class);
        $this->router = $router;
        $this->slugger = new AsciiSlugger();
    }

    public function getFile(int $id): ?FileEntity
    {
        return $this->fileRepo->find($id);
    }

    public function getImage(int $id): ?Image
    {
        return $this->imageRepo->find($id);
    }

    public function copy(FileEntity $file): FileEntity
    {
        $path = null;

        if (null !== $file->getFile()) {
            $path = $file->getFile()->getPathname();
        }
        else {
            $path = $this->getAbsolutePath($file);
        }

        if (null !== $path) {
            $contents = file_get_contents($path);

            $copy = $this->createFromContents($contents);
            $copy->setName($file->getName());

            return $copy;
        }

        return null;
    }

    public function createFromContents(string $contents): FileEntity
    {
        $path = @tempnam($this->absoluteUploadDir, 'tmp');

        $handle = fopen($path, 'w');
        fwrite($handle, $contents);
        fclose($handle);

        $httpfile = new HttpFile($path);

        $file = new FileEntity();
        $file->setFile($httpfile);

        return $file;
    }

    public function getMimeType(FileEntity $file)
    {
        if (null !== $file->getFile()) {
            return $file->getFile()->getMimeType();
        }

        $path = $this->getAbsolutePath($file);

        if (null === $path) {
            return null;
        }

        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($path);
    }

    public function getAbsolutePath(FileEntity $file): ?string
    {
        return null !== $file->getPath()
            ? $this->absoluteUploadDir . '/' . $file->getPath()
            : null;
    }

    public function getWebPath(FileEntity $file): ?string
    {
        $path = [$file->getName()];

        $folder = $file->getFolder();

        while ($folder) {
            $path[] = $folder->getName();

            $folder = $folter->getFolder();
        }

        return $this->router->generate('oh_media_file_read', [
            'id' => $file->getId(),
            'path' => implode('/', $path)
        ]);
    }

    public function preSaveFile(FileEntity $file)
    {
        $httpFile = $file->getFile();

        if (null === $httpFile) {
            return;
        }

        $this->setFileDimensions($file, $httpFile);

        if ($httpFile instanceof UploadedFile) {
            $name = $this->slugger->slug($httpFile->getClientOriginalName());

            $file->setName($name);
        }

        $ext = $httpFile->guessExtension();

        if ($ext) {
            $ext = '.' . $ext;
        }

        $now = new DateTime();

        $basename = $now->format('His');

        $path = $now->format('Y/m/d');

        $fullPath = $this->absoluteUploadDir . '/' . $path;

        $this->fileSystem->mkdir($fullPath);

        $i = 1;
        $temp = $basename;
        while(glob("$fullPath/$temp.*")) {
            $temp = $basename . '-' . $i;

            $i++;
        }

        $path .= '/' . $temp . $ext;

        $file->setPath($path);

        $mimeType = $this->getMimeType($file);

        $file->setMimeType($mimeType);
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

    public function postSaveFile(FileEntity $file)
    {
        if (null === $file->getPath() || null === $file->getFile()) {
            return;
        }

        $path = explode('/', $file->getPath());
        $name = array_pop($path);
        $path = implode('/', $path);

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $file->getFile()->move($this->absoluteUploadDir . '/' . $path, $name);

        $this->doImageProcessing($file);

        // check if we have an old file
        if ($file->getOldPath()) {
            // delete the old file
            $this->fileSystem->remove($this->absoluteUploadDir . '/' . $file->getOldPath());
            // clear the temp file path
            $file->setOldPath(null);
        }

        $file->clearFile();
    }

    public function postRemoveFile(FileEntity $file)
    {
        $filepath = $this->getAbsolutePath($file);

        $this->fileSystem->remove($filepath);
    }

    public function postSaveImageResize(ImageResize $resize)
    {
        $sourceFile = $resize->getImage()->getFile();

        $sourceFilepath = $this->getAbsolutePath($sourceFile);

        $imageResource = ImageResource::create($sourceFilepath);

        if (!$imageResource) {
            return;
        }

        $width = $resize->getWidth();
        $height = $resize->getHeight();

        $imageResource->resize($width, $height);

        $file = $resize->getFile();

        $filepath = $this->getAbsolutePath($file);

        $imageResource->save($filepath);
    }

    public function getImageResize(
        Image $image,
        ?int $width = null,
        ?int $height = null
    ): ?ImageResize
    {
        if (null === $width && null === $height) {
            return null;
        }

        $origWidth = $image->getWidth();
        $origHeight = $image->getHeight();

        if (!$origWidth || !$origHeight) {
            // something is not right, don't try to resize
            return null;
        }

        if (null === $width) {
            $width = ImageUtil::getTargetWidth(
                $origWidth,
                $origHeight,
                $height
            );
        }
        else if (null === $height) {
            $height = ImageUtil::getTargetWidth(
                $origWidth,
                $origHeight,
                $width
            );
        }

        $name = sprintf('%sx%s', $width, $height);

        $resize = $image->getResize($name);

        if (!$resize) {
            $copy = $this->copy($image->getFile());

            $copyName = $copy->getName();

            if (false !== strpos($copyName, '.')) {
                $parts = explode('.', $copyName);
                $parts[count($parts) - 2] .= '-' . $name;
                $copyName = implode('.', $parts);
            }
            else {
                $copyName .= '-' .$name;
            }

            $copy->setName($copyName);

            $resize = new ImageResize();
            $resize
                ->setFile($copy)
                ->setName($name)
                ->setWidth($width)
                ->setHeight($height)
                ->setImage($image);

            $this->em->persist($resize);
            $this->em->flush();
        }

        return $resize;
    }

    private function doImageProcessing(FileEntity $file)
    {
        $filepath = $this->getAbsolutePath($file);

        $imageResource = ImageResource::create($filepath);

        if (!$imageResource) {
            return;
        }

        $imageResource->fixOrientation()->save();
    }
}
