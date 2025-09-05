<?php

namespace OHMedia\FileBundle\Controller\Backend;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class FileDownloadController extends AbstractController
{
    public function __construct(
        private FileBrowser $fileBrowser,
        private FileManager $fileManager,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    #[Route('/files/download', name: 'file_download', methods: ['GET'])]
    public function download(): Response
    {
        if (!$this->getUser() || !$this->getUser()->isTypeDeveloper()) {
            throw $this->createAccessDeniedException('You cannot download the files.');
        }

        $zipFile = $this->projectDir.'/'.uniqid().'.zip';

        $zipArchive = new \ZipArchive();

        $zipArchive->open($zipFile, \ZipArchive::CREATE);

        $zipArchive->addEmptyDir('files');

        $items = $this->fileBrowser->getListing();

        foreach ($items as $item) {
            $this->addToZipArchive($zipArchive, $item, 'files/');
        }

        $zipArchive->close();

        $zip = file_get_contents($zipFile);

        unlink($zipFile);

        $response = new Response($zip);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="files.zip"');

        return $response;
    }

    private function addToZipArchive(
        \ZipArchive $zipArchive,
        File|FileFolder $item,
        string $path,
    ): void {
        if ($item instanceof File) {
            $filename = sprintf(
                '%s-%s.%s',
                $item->getName(),
                $item->getId(),
                $item->getExt(),
            );

            $zipArchive->addFile(
                $this->fileManager->getAbsolutePath($item),
                $path.$filename,
            );
        } else {
            $folderPath = $path.$item->getName();

            $zipArchive->addEmptyDir($folderPath);

            $items = $this->fileBrowser->getListing($item);

            foreach ($items as $item) {
                $this->addToZipArchive($zipArchive, $item, $folderPath.'/');
            }
        }
    }
}
