<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route('/f/{token}/{path}', name: 'oh_media_file_view', requirements: ['path' => '.+'], methods: ['GET'])]
    public function view(FileManager $fileManager, string $token, string $path = ''): Response
    {
        $file = $fileManager->getFileByToken($token);

        $response = $fileManager->response($file);

        if (!$response) {
            throw $this->createNotFoundException('File not found');
        }

        return $response;
    }
}
