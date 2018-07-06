<?php

namespace App\Repository;

use App\Entity\TaskDiscuss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TaskDiscuss|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskDiscuss|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskDiscuss[]    findAll()
 * @method TaskDiscuss[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskDiscussRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TaskDiscuss::class);
    }

//    /**
//     * @return TaskDiscuss[] Returns an array of TaskDiscuss objects
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
    public function findOneBySomeField($value): ?TaskDiscuss
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
