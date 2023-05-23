<?php

namespace OHMedia\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\FileBundle\Entity\ImageResize;

/**
 * @method ImageResize|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImageResize|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImageResize[]    findAll()
 * @method ImageResize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageResizeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImageResize::class);
    }

    public function save(ImageResize $imageResize, bool $flush = false): void
    {
        $this->getEntityManager()->persist($imageResize);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ImageResize $imageResize, bool $flush = false): void
    {
        $this->getEntityManager()->remove($imageResize);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
