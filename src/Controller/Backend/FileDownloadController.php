<?php

namespace OHMedia\FileBundle\Controller\Backend;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class FileDownloadController extends AbstractController
{
    public function __construct(
        private FileBrowser $fileBrowser,
        private FileManager $fileManager,
    ) {
    }

    #[Route('/files/download', name: 'file_download', methods: ['GET'])]
    public function __invoke(): Response
    {
        if (!$this->getUser() || !$this->getUser()->isTypeDeveloper()) {
            throw $this->createAccessDeniedException('You cannot download the files.');
        }

        $zipFile = __DIR__.microtime().'.zip';

        $zipArchive = new \ZipArchive();

        $zipArchive->open($zipFile, \ZipArchive::CREATE);

        $zipArchive->addEmptyDir('files');

        $items = $this->fileBrowser->getListing();

        foreach ($items as $item) {
            $this->addToZipArchive($zipArchive, $item, 'files/');
        }
    }

    private function addToZipArchive(
        ZipArchive $zipArchive,
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
