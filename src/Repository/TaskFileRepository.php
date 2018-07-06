<?php

namespace App\Repository;

use App\Entity\TaskFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TaskFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskFile[]    findAll()
 * @method TaskFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TaskFile::class);
    }

//    /**
//     * @return TaskFile[] Returns an array of TaskFile objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TaskFile
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
