<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;

/**
 * @ORM\Entity(repositoryClass="Strut\StrutBundle\Repository\UserGroupRepository")
 * @ORM\Table(name="fos_user_group")
 */
class UserGroup
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="role", type="integer")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Strut\StrutBundle\Entity\User", inversedBy="groupsUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Strut\StrutBundle\Entity\Group", inversedBy="groupsUser")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @ORM\Column(name="accepted", type="boolean", options={"default" : false})
     */
    private $accepted;

    /**
     * UserGroup constructor.
     * @param User $user
     * @param Group $group
     * @param $role
     */
    public function __construct(User $user, Group $group, $role, $request = false)
    {
        $this->user = $user;
        $this->group = $group;
        $this->role = $role;
        $this->accepted = $request;
    }

    /**
     * @return Group
     */
    public function getGroup(): GroupInterface
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param int $role
     * @return UserGroup
     */
    public function setRole(int $role): UserGroup
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @param bool $accepted
     */
    public function setAccepted(bool $accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * @return bool
     */
    public function getAccepted(): bool
    {
        return $this->accepted;
    }

}