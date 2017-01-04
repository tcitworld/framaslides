<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Strut\StrutBundle\Repository\GroupRepository")
 * @ORM\Table(name="fos_group")
 */
class Group extends BaseGroup
{
    /**
     * User Roles
     */

    /** User can only preview presentations */
    const ROLE_READ_ONLY = 1;

    /** User can create new presentations */
    const ROLE_WRITE = 2;

    /** User can manage all group presentations */
    const ROLE_MANAGE_PREZ = 3;

    /** User can manage users in the group */
    const ROLE_MANAGE_USERS = 5;

    /** User can rename and delete the group */
    const ROLE_ADMIN = 10;

    /**
     * Group join access
     */

    /** Any user can join the group */
    const ACCESS_OPEN = 1;

    /** An user needs to request to join the group */
    const ACCESS_REQUEST = 2;

    /** An user needs to be invited to join the group */
    const ACCESS_INVITATION_ONLY = 3;

    /** An user needs to be invited to join the group, and the group is not publicly listed */
    const ACCESS_HIDDEN = 4;


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", options={"default" : 1})
     */
    protected $acceptSystem;

    /**
     * @ORM\Column(type="integer", options={"default" : 2})
     */
    protected $defaultRole;

    /**
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="group", cascade={"persist"})
     */
    protected $users;

    public function __construct($name = '', array $roles = [])
    {
        parent::__construct($name, $roles);
        $this->defaultRole = self::ROLE_READ_ONLY;
        $this->acceptSystem = self::ACCESS_REQUEST;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers() {
        $userObj = new ArrayCollection();
        foreach ($this->users as $userGroup) {
            /** @var UserGroup $userGroup */
            $userObj->add($userGroup->getUser());
        }
        return $userObj;
    }

    /**
     * @return int
     */
    public function getDefaultRole(): int
    {
        return $this->defaultRole;
    }

    /**
     * @return int
     */
    public function getAcceptSystem(): int
    {
        return $this->acceptSystem;
    }

    /**
     * @param int $acceptSystem
     */
    public function setAcceptSystem(int $acceptSystem)
    {
        $this->acceptSystem = $acceptSystem;
    }
}
