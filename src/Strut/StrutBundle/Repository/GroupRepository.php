<?php

namespace Strut\StrutBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository
{
    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @return QueryBuilder
     */
    public function getBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('g');
    }
}
