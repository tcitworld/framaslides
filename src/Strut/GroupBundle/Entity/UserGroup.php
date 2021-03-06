<?php

namespace Strut\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;
use Strut\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Strut\GroupBundle\Repository\UserGroupRepository")
 * @UniqueEntity({"user_id", "group_id"})
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
     * @ORM\ManyToOne(targetEntity="Strut\UserBundle\Entity\User", inversedBy="userGroups")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Strut\GroupBundle\Entity\Group", inversedBy="users")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @ORM\Column(name="accepted", type="boolean", options={"default" : false})
     */
    private $accepted;

    /**
     * @ORM\OneToOne(targetEntity="Strut\GroupBundle\Entity\Invitation", inversedBy="userGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="invitation", referencedColumnName="code")
     */
    protected $invitation;

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
    public function isAccepted(): bool
    {
        return $this->accepted;
    }

	/**
	 * @param $invitation
	 */
	public function setInvitation(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

	/**
	 * @return Invitation
	 */
	public function getInvitation()
    {
        return $this->invitation;
    }

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id)
	{
		$this->id = $id;
	}
}
