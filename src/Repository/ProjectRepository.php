<?php
namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function getProjectsOfUser(User $user)
    {
        $qb = $this->createQueryBuilder('p')
                      ->select('partial p.{id,name}')
                      ->orderBy('p.name', 'ASC')
                      ->where('p.archive = false')
                      ->innerJoin('p.users', 'u', Query\Expr\Join::WITH, 'u.id = :user')
                      ->setParameter('user', $user->getId());

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1);

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    public function getProjects($archive = false)
    {
        $qb = $this->createQueryBuilder('p')
                      ->select('partial p.{id,name}')
                      ->where('p.archive = ' . ($archive ? 'true' : 'false'))
                      ->orderBy('p.name', 'ASC');

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1);

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

//    /**
//     * @return Project[] Returns an array of Project objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Project
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
