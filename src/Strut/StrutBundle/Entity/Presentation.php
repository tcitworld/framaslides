<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Strut\StrutBundle\Entity\Version;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Exclude;

/**
 * Presentation.
 *
 * @ORM\Entity(repositoryClass="Strut\StrutBundle\Repository\PresentationRepository")
 * @ORM\Table(name="`presentation`")
 * @ORM\HasLifecycleCallbacks()
 */
class Presentation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     *
     */
    private $title;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="Version", mappedBy="presentation", cascade={"remove"})
     */
    private $versions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Picture", mappedBy="presentation", cascade={"remove"})
     */
    private $pictures;

    /**
     * @var string
     *
     * @ORM\Column(name="rendered", type="text", nullable=true)
     */
    private $rendered;

    /**
     * @var string
     *
     * @ORM\Column(name="preview_config", type="text", nullable=true)
     */
    private $previewConfig;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_template", type="boolean", nullable=true, options={"default" = false})
     */
    private $isTemplate;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=true, options={"default" = false})
     *
     */
    private $isPublic;

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="text", nullable=true)
     *
     */
    private $uuid;

    /**
     * @Exclude
     * @ORM\ManyToOne(targetEntity="Strut\StrutBundle\Entity\User", inversedBy="presentations")
     *
     */
    private $user;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->versions = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return ArrayCollection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param ArrayCollection $versions
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
    }

    /**
     * Add new version.
     *
     * @param Version $version
     */
    public function addVersion(Version $version)
    {
        $this->versions[] = $version;
        $version->setPresentation($this);
    }

    /**
     * @return string
     */
    public function getRendered()
    {
        return $this->rendered;
    }

    /**
     * @param string $rendered
     */
    public function setRendered($rendered)
    {
        $this->rendered = $rendered;
    }

    /**
     * @return string
     */
    public function getPreviewConfig()
    {
        return $this->previewConfig;
    }

    /**
     * @param string $previewConfig
     */
    public function setPreviewConfig($previewConfig)
    {
        $this->previewConfig = $previewConfig;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastVersionDate()
    {
        return $this->getLastVersion()->getUpdatedAt();
    }

    /**
     * @return Version
     * @throws \Exception
     */
    public function getLastVersion() {
        $lastVersion = $this->versions->first();
        if (!$lastVersion) {
            throw new \Exception('No version found for this presentation');
        }
        return $lastVersion;
    }

    public function getNbSlides() {
        $lastVersionContent = $this->getLastVersion()->getContent();
        $nbSlides = count(json_decode($lastVersionContent)->slides);
        return $nbSlides;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return boolean
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Remove version
     *
     * @param Version $version
     */
    public function removeVersion(Version $version)
    {
        $this->versions->removeElement($version);
    }

    /**
     * @return boolean
     */
    public function getIsTemplate()
    {
        return $this->isTemplate;
    }

    /**
     * @param boolean $isTemplate
     */
    public function setIsTemplate($isTemplate)
    {
        $this->isTemplate = $isTemplate;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return Presentation
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function generateUuid()
    {
        if (null === $this->uuid) {
            // @see http://blog.kevingomez.fr/til/2015/07/26/why-is-uniqid-slow/ for true parameter
            $this->uuid = uniqid('', true);
        }
    }

    public function cleanUuid()
    {
        $this->uuid = null;
    }

    /**
     * @return ArrayCollection<Picture>
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param mixed $pictures
     */
    public function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    public function addPicture(Picture $picture) {
        $this->pictures[] = $picture;
        $picture->setPresentation($this);
    }
}
