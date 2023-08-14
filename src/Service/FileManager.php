<?php

namespace OHMedia\FileBundle\Service;

use Doctrine\ORM\EntityManager;
use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Entity\ImageResize;
use OHMedia\FileBundle\Util\FileUtil;
use OHMedia\FileBundle\Util\ImageResource;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileManager
{
    public const FILE_DIR = 'oh_media_files';

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
    ) {
        $this->absoluteUploadDir = $projectDir.'/'.static::FILE_DIR;
        $this->em = $em;
        $this->fileRepo = $em->getRepository(FileEntity::class);
        $this->fileSystem = new FileSystem();
        $this->imageRepo = $em->getRepository(Image::class);
        $this->router = $router;
        $this->slugger = new AsciiSlugger();
    }

    public function response(?FileEntity $file): ?BinaryFileResponse
    {
        if (!$file) {
            return null;
        }

        $physicalFile = $this->getAbsolutePath($file);

        if (!file_exists($physicalFile)) {
            return null;
        }

        $mimeType = $file->getMimeType();

        $maliciousMimeTypes = [
            'application/x-httpd-php',
            'application/xhtml+xml',
            'text/html',
        ];

        if (in_array($mimeType, $maliciousMimeTypes)) {
            $mimeType = 'text/plain';
        }

        $response = new BinaryFileResponse($physicalFile);
        $response->headers->set('Content-Type', $mimeType);

        BinaryFileResponse::trustXSendfileTypeHeader();

        return $response;
    }

    public function upload(HttpFile ...$httpFiles): JsonResponse
    {
        $json = ['files' => []];

        $files = [];
        foreach ($httpFiles as $httpFile) {
            $file = new FileEntity();
            $file
                ->setFile($httpFile)
                ->setTemporary(true)
            ;

            $this->fileRepo->save($file, true);

            $files[] = $file;
        }

        foreach ($files as $file) {
            $json['files'][] = [
                'id' => $file->getId(),
                'name' => $file->getFilename(),
                'path' => $this->getWebPath($file),
            ];
        }

        return new JsonResponse($json);
    }

    public function getFileByToken(string $token): ?FileEntity
    {
        return $this->fileRepo->findOneByToken($token);
    }

    public function getImage(int $id): ?Image
    {
        return $this->imageRepo->find($id);
    }

    public function copy(FileEntity $file): ?FileEntity
    {
        $path = null;

        if (null !== $file->getFile()) {
            $path = $file->getFile()->getPathname();
        } else {
            $path = $this->getAbsolutePath($file);
        }

        if (null !== $path) {
            $contents = file_get_contents($path);

            $copy = $this->createFromContents($contents);
            $copy
                ->setName($file->getName())
                ->setExt($file->getExt())
            ;

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
            ? $this->absoluteUploadDir.'/'.$file->getPath()
            : null;
    }

    public function getWebPath(FileEntity $file): ?string
    {
        $filename = $file->getFilename();

        $token = $file->getToken();

        if (!$filename || !$token) {
            return null;
        }

        $path = [$filename];

        $folder = $file->getFolder();

        while ($folder) {
            array_unshift($path, $folder->getName());

            $folder = $folder->getFolder();
        }

        return $this->router->generate('oh_media_file_view', [
            'token' => $token,
            'path' => implode('/', $path),
        ]);
    }

    private $newFiles = [];

    public function preSaveFile(FileEntity $file)
    {
        $httpFile = $file->getFile();

        if (null === $httpFile) {
            return;
        }

        $token = $this->generateFileToken();

        $file->setToken($token);

        $this->setFileDimensions($file, $httpFile);

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

        $basename = $now->format('His');

        $path = $now->format('Y/m/d');

        $fullPath = $this->absoluteUploadDir.'/'.$path;

        $this->fileSystem->mkdir($fullPath);

        $i = 1;
        $temp = $basename;
        while (glob("$fullPath/$temp.*") || isset($this->newFiles["$fullPath/$temp"])) {
            $temp = $basename.'-'.$i;

            ++$i;
        }

        $this->newFiles["$fullPath/$temp"] = 1;

        $path .= '/'.$temp.$ext;

        $mimeType = $this->getMimeType($file);

        $size = $httpFile->getSize();

        $file
            ->setPath($path)
            ->setMimeType($mimeType)
            ->setSize($size ?: null)
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
        } while ($this->fileRepo->findByToken($token));

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

    public function postSaveFile(FileEntity $file)
    {
        if ($file->getPath() && $file->getFile()) {
            $path = explode('/', $file->getPath());
            $name = array_pop($path);
            $path = implode('/', $path);

            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $file->getFile()->move($this->absoluteUploadDir.'/'.$path, $name);

            $this->doImageProcessing($file);
        }

        // check if we have an old file
        if ($file->getOldPath()) {
            // delete the old file
            $this->fileSystem->remove($this->absoluteUploadDir.'/'.$file->getOldPath());
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
        int $width = null,
        int $height = null
    ): ?ImageResize {
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
            $width = FileUtil::getTargetWidth(
                $origWidth,
                $origHeight,
                $height
            );
        } elseif (null === $height) {
            $height = FileUtil::getTargetHeight(
                $origWidth,
                $origHeight,
                $width
            );
        }

        $name = sprintf('%sx%s', $width, $height);

        $resize = $image->getResize($name);

        if (!$resize) {
            $copy = $this->copy($image->getFile());

            $copy
                ->setName($copy->getName().'-'.$name)
                ->setHidden(true)
            ;

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
