<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Security\Voter\FileVoter;
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

        if ($file->isLocked()) {
            $this->denyAccessUnlessGranted(
                FileVoter::VIEW,
                $file,
                'You cannot view this file.'
            );
        }

        return $response;
    }
}
