<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File as FileEntity;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileManager
{
    public const FILE_DIR = 'oh_media_files';

    private $absoluteUploadDir;
    private $router;

    public function __construct(
        UrlGeneratorInterface $router,
        string $projectDir
    ) {
        $this->absoluteUploadDir = $projectDir.'/'.static::FILE_DIR;
        $this->router = $router;
    }

    public function getAbsoluteUploadDir(): string
    {
        return $this->absoluteUploadDir;
    }

    public function isValidUploadFilepath(?string $filepath): bool
    {
        if (!$filepath) {
            return false;
        }

        // Make sure we are staying within the upload directory by
        // preventing the use of the ".." path.
        // This should never happen, but better safe than sorry!
        if (str_contains($filepath, '..')) {
            return false;
        }

        return true;
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

        if ($folder = $file->getFolder()) {
            $path = $folder->getPath().'/'.$filename;
        } else {
            $path = $filename;
        }

        return $this->router->generate('oh_media_file_view', [
            'token' => $token,
            'path' => $path,
        ]);
    }
}
