<?php

namespace Strut\StrutBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Strut\StrutBundle\Entity\User;

class UserGroupRepository extends EntityRepository
{
    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @return QueryBuilder
     */
    public function getBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u');
    }
}
