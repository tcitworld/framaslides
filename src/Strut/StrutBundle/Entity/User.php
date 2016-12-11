<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Strut\StrutBundle\Entity\Presentation;

/**
 * @ORM\Entity
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
     * @ORM\OneToMany(targetEntity="Strut\StrutBundle\Entity\Presentation", mappedBy="user", cascade={"remove"})
     */
    protected $presentations;

    /**
     * @var Config
     *
     * @ORM\OneToMany(targetEntity="Strut\StrutBundle\Entity\Config", mappedBy="user", cascade={"remove"})
     */
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $this->presentations = new ArrayCollection();
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
    public function getName(): string
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
     * @param Presentation $entry
     *
     * @return User
     */
    public function addPresentation(Presentation $entry): User
    {
        $this->presentations[] = $entry;

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
    public function getConfig(): Config
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
}
