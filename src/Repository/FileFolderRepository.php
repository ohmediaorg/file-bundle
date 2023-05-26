<?php

namespace OHMedia\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\FileBundle\Entity\FileFolder;

/**
 * @method FileFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileFolder[]    findAll()
 * @method FileFolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileFolder::class);
    }

    public function save(FileFolder $fileFolder, bool $flush = false): void
    {
        $this->getEntityManager()->persist($fileFolder);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileFolder $fileFolder, bool $flush = false): void
    {
        $this->getEntityManager()->remove($fileFolder);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
