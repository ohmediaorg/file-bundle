<?php

namespace OHMedia\FileBundle\Cleanup;

use OHMedia\CleanupBundle\Interfaces\CleanerInterface;
use OHMedia\FileBundle\Repository\FileRepository;
use Symfony\Component\Console\Output\OutputInterface;

class FileCleaner implements CleanerInterface
{
    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function __invoke(OutputInterface $output): void
    {
        $this->fileRepository->deleteTemporary();
    }
}
