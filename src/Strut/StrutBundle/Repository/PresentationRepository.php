<?php
/**
 * Created by PhpStorm.
 * User: tcit
 * Date: 15/11/16
 * Time: 17:51
 */

namespace Strut\StrutBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Strut\StrutBundle\Entity\Group;
use Strut\StrutBundle\Entity\User;

class PresentationRepository extends EntityRepository
{
    /**
     * Return a query builder to used by other getBuilderFor* method.
     *
     * @param User $user
     * @return QueryBuilder
     *
     */
    public function getBuilderByUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.id = :userId')->setParameter('userId', $user->getId())
            ->orderBy('p.createdAt', 'desc')
            ;
    }

    public function getBuilderForTemplatesByUser(User $user): QueryBuilder
    {
        return $this->getBuilderByUser($user)
            ->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
            ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', false)
            ->orderBy('p.createdAt', 'desc')
            ;
    }

    public function getBuilderForPublicTemplatesByUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.id != :userId')->setParameter('userId', $user->getId())
            ->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
            ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', true)
            ->orderBy('p.createdAt', 'desc')
            ;
    }

    public function getBuilderForPublishedTemplatesByUser(User $user): QueryBuilder
    {
        return $this->getBuilderByUser($user)
            ->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
            ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', true)
            ->orderBy('p.createdAt', 'desc')
        ;
    }

    public function getBuilderForSearchByUser(User $user, string $term, string $currentRoute): QueryBuilder
    {
        $qb = $this->getBuilderByUser($user);

        if ('templates' === $currentRoute) {
            $qb->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
                ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', false);
        } elseif ('templates-published' === $currentRoute) {
            $qb = $this->getBuilderForPublicTemplatesByUser($user);
        } elseif ('templates-public' === $currentRoute) {
            $qb->andWhere('p.isTemplate = :isTemplate')->setParameter('isTemplate', true)
                ->andWhere('p.isPublic = :isPublic')->setParameter('isPublic', true);
        }

        $qb->andWhere('v.content LIKE :term OR p.title LIKE :term')->setParameter('term', '%'.$term.'%')
            ->leftJoin('p.versions', 'v')
            ->groupBy('p.id');

        return $qb;
    }

    public function findByGroup(Group $group): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.groupShares', 'g', 'WITH', 'g.id = :group')
            ->setParameter(':group', $group->getId());
    }

    public function findAllGroupShared(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->join('p.groupShares', 'g');
    }

    public function removeAllByUser(User $user)
    {
        $this->getEntityManager()
            ->createQuery('DELETE FROM Strut\StrutBundle\Entity\Presentation p WHERE p.user = :userId')
            ->setParameter('userId', $user->getId())
            ->execute();
    }
}
