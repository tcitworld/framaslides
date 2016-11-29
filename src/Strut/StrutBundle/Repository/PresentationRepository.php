<?php
/**
 * Created by PhpStorm.
 * User: tcit
 * Date: 15/11/16
 * Time: 17:51
 */

namespace Strut\StrutBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Strut\StrutBundle\Entity\User;

class PresentationRepository extends EntityRepository
{
    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getBuilderByUser($userId)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.id = :userId')->setParameter('userId', $userId)
            ->orderBy('e.createdAt', 'desc')
            ;
    }

    public function getTemplates(User $user) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.id = :userId')->setParameter('userId', $user->getId())
            ->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
            ->orderBy('p.createdAt', 'desc')
            ->getQuery()
            ;
        return $query->getResult();
    }

    public function getPublicTemplates(User $user) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.id != :userId')->setParameter('userId', $user->getId())
            ->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
            ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', true)
            ->orderBy('p.createdAt', 'desc')
            ->getQuery()
            ;
        return $query->getResult();
    }

    public function getPublishedTemplates(User $user) {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.id = :userId')->setParameter('userId', $user->getId())
            ->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
            ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', true)
            ->orderBy('p.createdAt', 'desc')
            ->getQuery()
        ;
        return $query->getResult();
    }
}