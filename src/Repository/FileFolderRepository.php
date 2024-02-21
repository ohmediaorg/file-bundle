<?php

namespace OHMedia\FileBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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

    public function countByName(string $name, FileFolder $folder)
    {
        $params = [
            new Parameter('name', $name),
        ];

        $qb = $this->createQueryBuilder('ff')
            ->select('COUNT(ff.id)')
            ->where('ff.name = :name');

        if ($id = $folder->getId()) {
            $qb->andWhere('ff.id <> :id');

            $params[] = new Parameter('id', $id);
        }

        if ($parent = $folder->getFolder()) {
            $qb->andWhere('ff.folder = :parent');

            $params[] = new Parameter('parent', $parent);
        }

        return $qb->setParameters(new ArrayCollection($params))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
