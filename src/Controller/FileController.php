<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Security\Voter\FileVoter;
use OHMedia\FileBundle\Service\FileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route('/f/{token}/{path}', name: 'oh_media_file_view', requirements: ['path' => '.+'], methods: ['GET'])]
    public function view(
        FileResponse $fileResponse,
        FileRepository $fileRepository,
        string $token,
        string $path = ''
    ): Response {
        $file = $fileRepository->findOneByToken($token);

        if (!$file) {
            throw $this->createNotFoundException('File not found.');
        }

        $response = $fileResponse->get($file);

        if (!$response) {
            throw $this->createNotFoundException('File not found.');
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
