<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="config")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Strut\StrutBundle\Entity\User", inversedBy="config")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="text", nullable=true)
     */
    private $lang;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->lang = 'fr';
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }
}
