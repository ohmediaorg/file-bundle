<?php

namespace JstnThms\FileBundle\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JstnThms\FileBundle\Entity\File;

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

    public function deleteTemporary()
    {
        $yesterday = new DateTime('-1 day');
        
        return $this->createQueryBuilder('f')
            ->delete()
            ->where('f.temporary = 1')
            ->andWhere('f.created_at < :yesterday')
            ->setParameter('yesterday', $yesterday)
            ->getQuery()
            ->execute();
    }
}
