<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Strut\StrutBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Presentation", mappedBy="user", cascade={"remove"})
     */
    protected $presentations;

    /**
     * @var Config
     *
     * @ORM\OneToOne(targetEntity="Config", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $config;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $userGroups;

    public function __construct()
    {
        parent::__construct();
        $this->presentations = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->timestamps();
        $this->roles = ['ROLE_USER'];
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function timestamps()
    {
        if (is_null($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @param Presentation $presentation
     * @return User
     */
    public function addPresentation(Presentation $presentation): User
    {
        $this->presentations[] = $presentation;

        return $this;
    }

    /**
     * @return ArrayCollection<Entry>
     */
    public function getPresentations(): ArrayCollection
    {
        return $this->presentations;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        return $this->username === $user->getUsername();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getUsername();
    }


    /**
     * Set config.
     *
     * @param Config $config
     *
     * @return User
     */

    public function setConfig(Config $config = null)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return User
     */
    public function setUpdatedAt(\DateTime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Remove presentation
     *
     * @param Presentation $presentation
     */
    public function removePresentation(Presentation $presentation)
    {
        $this->presentations->removeElement($presentation);
    }

    public function addAGroup(Group $group, $role)
    {
       $this->userGroups[] = new UserGroup($this, $group, $role);
    }

    public function getUserGroupFromGroup(Group $group): UserGroup
    {
        foreach ($this->userGroups as $userGroup) {
            if ($userGroup->getGroup() == $group) {
                return $userGroup;
            }
        }
        throw new \Exception('No such group');
    }

    public function setGroupRole(Group $group, $role)
    {
        if ($userGroup = $this->getUserGroupFromGroup($group)) {
            $userGroup->setRole($role);
        }
    }

    public function getGroupRoleForUser(Group $group)
    {
        if ($userGroup = $this->getUserGroupFromGroup($group)) {
            return $userGroup->getRole();
        }
        return null;
    }

    public function acceptedInGroup(Group $group): bool
    {
        if ($group::ACCESS_REQUEST === $group->getAcceptSystem()) {
            $userGroup = $this->getUserGroupFromGroup($group);
            return $userGroup->getAccepted();
        }
        return true;
    }
}
