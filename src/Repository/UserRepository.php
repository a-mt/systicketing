<?php
namespace App\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUsers()
    {
        $qb = $this->createQueryBuilder('u')
                      ->select('partial u.{id,firstName,lastName}')
                      ->orderBy('u.firstName,u.lastName', 'ASC');

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, 1);

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    public function getUsersOfProjectQb($projectId)
    {
        return $this->createQueryBuilder('u')
                      ->select('partial u.{id,firstName,lastName}')
                      ->innerJoin('u.projects', 'p', Query\Expr\Join::WITH, 'p.id = :project')
                      ->orderBy('u.firstName,u.lastName', 'ASC')
                      ->setParameter('project', $projectId);
    }
}