<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File as FileEntity;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Repository\FileFolderRepository;
use OHMedia\FileBundle\Repository\FileRepository;

class FileBrowser
{
    private int $limitBytes;

    public function __construct(
        private FileRepository $fileRepository,
        private FileFolderRepository $fileFolderRepository,
        private bool $enabled,
        int $limitMb
    ) {
        $this->limitBytes = $limitMb * 1024 * 1024;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLimitBytes(): int
    {
        return $this->limitBytes;
    }

    public function getUsageBytes(): int
    {
        return (int) $this->fileRepository->createQueryBuilder('f')
            ->select('SUM(f.size)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getListing(FileFolder $parent = null): array
    {
        $fileQueryBuilder = $this->fileRepository
            ->createQueryBuilder('f')
            ->where('f.browser = 1');

        if ($parent) {
            $fileQueryBuilder
                ->andWhere('f.folder = :folder')
                ->setParameter('folder', $parent);
        } else {
            $fileQueryBuilder->andWhere('IDENTITY(f.folder) IS NULL');
        }

        $files = $fileQueryBuilder
            ->orderBy('LOWER(f.name)', 'ASC')
            ->getQuery()
            ->getResult();

        $fileFolderQueryBuilder = $this->fileFolderRepository
            ->createQueryBuilder('ff')
            ->where('ff.browser = 1');

        if ($parent) {
            $fileFolderQueryBuilder
                ->andWhere('ff.folder = :folder')
                ->setParameter('folder', $parent);
        } else {
            $fileFolderQueryBuilder->andWhere('IDENTITY(ff.folder) IS NULL');
        }

        $folders = $fileFolderQueryBuilder
            ->orderBy('LOWER(ff.name)', 'ASC')
            ->getQuery()
            ->getResult();

        // TODO: potential user preferences?
        $filesFirst = false;
        $foldersFirst = false;

        if ($filesFirst) {
            $items = array_merge($files, $folders);
        } elseif ($foldersFirst) {
            $items = array_merge($folders, $files);
        } else {
            $items = array_merge($files, $folders);

            usort($items, function ($a, $b) {
                $aProp = $a instanceof FileEntity
                    ? $a->getFilename()
                    : $a->getName();

                $bProp = $b instanceof FileEntity
                    ? $b->getFilename()
                    : $b->getName();

                return strtolower($aProp) <=> strtolower($bProp);
            });
        }

        return $items;
    }
}
