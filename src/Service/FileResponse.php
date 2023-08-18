<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File as FileEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileResponse
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function get(FileEntity $file): ?BinaryFileResponse
    {
        $physicalFile = $this->fileManager->getAbsolutePath($file);

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
}
