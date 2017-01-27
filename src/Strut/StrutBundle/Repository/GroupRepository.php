<?php

namespace Strut\StrutBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Strut\StrutBundle\Entity\User;

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

    public function findPublicGroups(): QueryBuilder
    {
        return $this->getBuilder()
            ->where('g.acceptSystem < 10');
    }

    public function findPublicGroupsByName(string $search): QueryBuilder
	{
		return $this->getBuilder()
			->where('g.acceptSystem < 10')
			->andWhere('g.name LIKE :name')
			->setParameter(':name', '%' . $search . '%');
	}

    public function findGroupsByUser(User $user): QueryBuilder
    {
        return $this->getBuilder()
            ->join('Strut\StrutBundle\Entity\UserGroup', 'u', 'WITH', 'u.group = g.id')
            ->where('u.user = :user')->setParameter(':user', $user->getId());
    }
}
