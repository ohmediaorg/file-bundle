<?php

namespace OHMedia\FileBundle\Cleanup;

use OHMedia\CleanupBundle\Interfaces\CleanerInterface;
use OHMedia\FileBundle\Service\FileManager;
use Symfony\Component\Console\Output\OutputInterface;

class DirectoryCleaner implements CleanerInterface
{
    public function __construct(private FileManager $fileManager)
    {
    }

    public function __invoke(OutputInterface $output): void
    {
        $directory = $this->fileManager->getAbsoluteUploadDir();

        foreach ($this->getDirectoryItems($directory) as $item) {
            if (is_dir($directory.'/'.$item)) {
                $this->cleanDir($directory.'/'.$item);
            }
        }
    }

    private function cleanDir(string $directory): void
    {
        foreach ($this->getDirectoryItems($directory) as $item) {
            if (is_dir($directory.'/'.$item)) {
                $this->cleanDir($directory.'/'.$item);
            }
        }

        // scan it again to see if it is empty
        $children = 0;

        foreach ($this->getDirectoryItems($directory) as $item) {
            ++$children;
        }

        if (0 === $children) {
            rmdir($directory);
        }
    }

    private function getDirectoryItems(string $directory): array
    {
        $items = [];

        foreach (scandir($directory) as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}
