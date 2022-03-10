<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File as HttpFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController
{
    private $manager;

    public function __construct(FileManager $manager)
    {
        $this->manager = $manager;
    }

    #[Route('/f/{id}/{path}', name: 'oh_media_file_read', methods: ['GET'])]
    public function readAction(
        Request $request,
        int $id,
        string $path = ''
    ): Response
    {
        $file = $this->manager->get($id);

        if (!$file) {
            // TODO: file doesn't exist
            exit('no find');
        }

        $physicalFile = $this->manager->getAbsolutePath($file);

        if (!file_exists($physicalFile)) {
            // TODO: file doesn't exist
            exit($physicalFile);
        }

        if ($file->getPrivate()) {
            // TODO: check access
            exit('no access');
        }

        $response = new BinaryFileResponse($physicalFile);
        $response->headers->set('Content-Type', $file->getMimeType());

        BinaryFileResponse::trustXSendfileTypeHeader();

        return $response;
    }

    #[Route('/file/upload', name: 'oh_media_file_upload', methods: ['POST'])]
    public function uploadAction(Request $request): Response
    {
        if (!$this->getUser()) {
            exit();
        }

        if (!$httpfiles = $request->files->get('files')) {
            exit;
        }

        $em = $this->getDoctrine()->getManager();

        $files = [];
        foreach ($httpfiles as $httpfile) {
            if (!$httpfile instanceof HttpFile) {
                continue;
            }

            $file = new File();
            $file->setFile($httpfile)
                ->setTemporary(true);

            $em->persist($file);

            $files[] = $file;
        }

        $em->flush();

        $json = ['files' => []];
        foreach ($files as $file) {
            $json['files'][] = [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'path' => $this->manager->getWebPath($file)
            ];
        }

        return new JsonResponse($json);
    }
}
