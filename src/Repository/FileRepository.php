<?php

namespace OHMedia\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\FileBundle\Entity\File;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function save(File $file, bool $flush = false): void
    {
        $this->getEntityManager()->persist($file);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(File $file, bool $flush = false): void
    {
        $this->getEntityManager()->remove($file);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
