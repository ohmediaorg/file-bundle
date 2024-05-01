<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File as FileEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileResponse
{
    public function __construct(private FileManager $fileManager)
    {
    }

    public function get(FileEntity $file): ?BinaryFileResponse
    {
        $filepath = $file->getPath();

        if (!$this->fileManager->isValidUploadFilepath($filepath)) {
            return null;
        }

        $uploadDir = $this->fileManager->getAbsoluteUploadDir();

        $physicalFile = $uploadDir.'/'.$filepath;

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
