<?php

namespace OHMedia\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\FileBundle\Entity\Image;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function save(Image $image, bool $flush = false): void
    {
        $this->getEntityManager()->persist($image);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Image $image, bool $flush = false): void
    {
        $this->getEntityManager()->remove($image);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
