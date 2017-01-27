<?php

namespace Strut\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Strut\GroupBundle\Entity\Group;

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

    public function findGroupRequests(Group $group): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userGroups', 'usergroup')
            ->where('usergroup.group = :group')->setParameter(':group', $group->getId())
            ->andWhere('usergroup.accepted = false');
    }

    public function findGroupMembers(Group $group): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userGroups', 'usergroup')
            ->where('usergroup.group = :group')->setParameter(':group', $group->getId())
            ->andWhere('usergroup.accepted = true');
    }

    public function searchUsers($search): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.username LIKE :search')
            ->orWhere('u.email LIKE :search')
            ->setParameter(':search', '%' . $search . '%');
    }
}
