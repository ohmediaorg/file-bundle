<?php

namespace JstnThms\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JstnThms\FileBundle\Entity\FileFolder;

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

    // /**
    //  * @return FileFolder[] Returns an array of FileFolder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FileFolder
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
