<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Strut\StrutBundle\Entity\Version;
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
     * @var PersistentCollection
     * @ORM\OrderBy({"updatedAt" = "DESC"})
     * @ORM\OneToMany(targetEntity="Version", mappedBy="presentation", cascade={"remove"})
     */
    private $versions;

    /**
     * @var PersistentCollection
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
        $this->user->addPresentation($this);
        $this->versions = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->isTemplate = false;
        $this->isPublic = false;
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

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return PersistentCollection
     */
    public function getVersions(): PersistentCollection
    {
        return $this->versions;
    }

    /**
     * @param ArrayCollection $versions
     */
    public function setVersions(ArrayCollection $versions)
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
    public function getRendered(): string
    {
        return $this->rendered ?? '';
    }

    /**
     * @param string $rendered
     */
    public function setRendered(string $rendered)
    {
        $this->rendered = $rendered;
    }

    /**
     * @return string
     */
    public function getPreviewConfig(): string
    {
        return $this->previewConfig;
    }

    /**
     * @param string $previewConfig
     */
    public function setPreviewConfig(string $previewConfig)
    {
        $this->previewConfig = $previewConfig;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getLastVersionDate(): \DateTime
    {
        return $this->getLastVersion()->getUpdatedAt();
    }

    /**
     * @return Version
     * @throws \Exception
     */
    public function getLastVersion(): Version
    {
        $lastVersion = $this->versions->first();
        if (!$lastVersion) {
            throw new \Exception('No version found for this presentation');
        }
        return $lastVersion;
    }

    public function getNbSlides(): int
    {
        $lastVersionContent = $this->getLastVersion()->getContent();
        $nbSlides = count(json_decode($lastVersionContent)->slides);
        return $nbSlides;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return boolean
     */
    public function isPublic(): bool
    {
        return $this->isPublic ?? false;
    }

    /**
     * @param boolean $isPublic
     */
    public function setPublic(bool $isPublic)
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
    public function isTemplate(): bool
    {
        return $this->isTemplate ?? false;
    }

    /**
     * @param boolean $isTemplate
     */
    public function setTemplate(bool $isTemplate)
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
    public function setUuid(string $uuid)
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
     * @return PersistentCollection <Picture>
     */
    public function getPictures(): PersistentCollection
    {
        return $this->pictures;
    }

    /**
     * @param mixed $pictures
     */
    public function setPictures(PersistentCollection $pictures)
    {
        $this->pictures = $pictures;
    }

    public function addPicture(Picture $picture)
    {
        $this->pictures[] = $picture;
        $picture->setPresentation($this);
    }
}
