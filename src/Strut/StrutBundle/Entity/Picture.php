<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * Picture.
 *
 * @ORM\Entity(repositoryClass="Strut\StrutBundle\Repository\PictureRepository")
 * @ORM\Table(name="`picture`")
 */
class Picture
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
     * @ORM\Column(name="uuid", type="text", nullable=true)
     *
     */
    private $uuid;

    /**
     * @var Presentation
     * @Exclude
     * @ORM\ManyToOne(targetEntity="Strut\StrutBundle\Entity\Presentation", inversedBy="pictures")
     */
    private $presentation;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * Picture settings
     */
    const PATH = 'assets/pics';
    const REGENERATE_PICTURES_QUALITY = 80;

    /**
     * @ORM\Column(name="filename", type="text")
     */
    private $fileName;

    /**
     * @var string
     * @ORM\Column(name="extension", type="string", nullable=true)
     */
    private $extension;

    /**
     * Picture constructor.
     * @param Presentation $presentation
     */
    public function __construct(Presentation $presentation = null)
    {
        $this->presentation = $presentation;
        $this->createdAt = new \DateTime();
        $this->uuid = uniqid('', true);
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
     * @param Presentation $presentation
     */
    public function setPresentation(Presentation $presentation)
    {
        $this->presentation = $presentation;
    }

    /**
     * @return Presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createAt
     */
    public function setCreatedAt(\DateTime $createAt)
    {
        $this->createdAt = $createAt;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }
}
