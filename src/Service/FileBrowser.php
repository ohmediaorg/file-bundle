<?php

namespace OHMedia\FileBundle\Service;

use OHMedia\FileBundle\Entity\File;
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

    public function getListing(
        ?FileFolder $parent = null,
        bool $includeImages = true,
        bool $includeFiles = true,
    ): array {
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

        if ($includeImages && $includeFiles) {
            // don't need to alter the query
        } elseif ($includeImages) {
            $fileQueryBuilder->andWhere('f.image = 1');
        } elseif ($includeFiles) {
            $fileQueryBuilder->andWhere('(f.image = 0 OR f.image IS NULL)');
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
                $aProp = $a instanceof File
                    ? $a->getFilename()
                    : $a->getName();

                $bProp = $b instanceof File
                    ? $b->getFilename()
                    : $b->getName();

                return strtolower($aProp) <=> strtolower($bProp);
            });
        }

        return $items;
    }

    public function getFolderChoices(
        ?FileFolder $parent = null,
        bool $includeParent = true,
        bool $includeSubfolders = true,
    ): array {
        $qb = $this->fileFolderRepository->createQueryBuilder('ff');
        $qb->where('ff.browser = 1');

        if ($parent && !$includeSubfolders) {
            $subfolders = $parent->getSubfolders();

            $ids = array_map(function ($folder) {
                return $folder->getId();
            }, $subfolders);

            if (!$includeParent) {
                $ids[] = $parent->getId();
            }

            $qb->andWhere('ff.id NOT IN (:ids)')
                ->setParameter('ids', $ids);
        } elseif ($parent && !$includeParent) {
            $qb->andWhere('ff.id <> :id')
                ->setParameter('id', $parent->getId());
        }

        $result = $qb->getQuery()->getResult();

        usort($result, function (FileFolder $a, FileFolder $b) {
            return $a->getPath() <=> $b->getPath();
        });

        return $result;
    }
}
