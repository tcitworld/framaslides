<?php

namespace Strut\StrutBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class UserRepository extends EntityRepository
{
    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'desc');
    }

    public function findAll(): array
    {
        return $this->findBy([], ['createdAt' => 'desc']);
    }

    /**
     * Count how many users are enabled.
     *
     * @return int
     */
    public function getSumEnabledUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u)')
            ->andWhere('u.enabled = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

}
