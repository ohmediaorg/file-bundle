<?php

namespace JstnThms\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JstnThms\FileBundle\Entity\ImageResize;

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

    // /**
    //  * @return ImageResize[] Returns an array of ImageResize objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImageResize
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
