<?php

namespace OHMedia\FileBundle\Service;

use Doctrine\ORM\QueryBuilder;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\UtilityBundle\Service\AbstractEntityPathProvider;

class FileEntityPathProvider extends AbstractEntityPathProvider
{
    public function __construct(
        private FileRepository $fileRepository,
        private FileManager $fileManager,
    ) {
    }

    public function getEntityClass(): string
    {
        return File::class;
    }

    public function getGroupLabel(): string
    {
        return 'Files';
    }

    public function getEntityQueryBuilder(?int $selectedEntityId): QueryBuilder
    {
        $qb = $this->fileRepository
            ->createQueryBuilder('f')
            ->where('(f.browser = 1 AND f.locked = 0)')
            ->orWhere('f.id = :id')
            ->setParameter('id', $selectedEntityId)
            ->orderBy('f.name', 'ASC');

        if ($selectedEntityId) {
            $qb->orWhere('f.id = :id')
                ->setParameter('id', $selectedEntityId);
        }

        return $qb;
    }

    public function getEntityPath(mixed $entity): ?string
    {
        if ($entity->isLocked()) {
            return null;
        }

        return $this->fileManager->getWebPath($entity);
    }
}
