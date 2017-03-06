<?php

namespace Strut\StrutBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Strut\UserBundle\Entity\User;

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
     * @ORM\OneToOne(targetEntity="Strut\UserBundle\Entity\User", inversedBy="config")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="text", nullable=true)
     */
    private $lang;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="list_mode", type="integer", nullable=true)
	 */
	private $listMode = self::LIST;

	const CARDS = 0;
	const LIST = 1;

	/**
	 * Config constructor.
	 * @param UserInterface $user
	 */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
        $this->lang = 'fr';
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLanguage(string $lang)
    {
        $this->lang = $lang;
    }

	/**
	 * @return int
	 */
	public function getListMode()
	{
		return $this->listMode;
	}

	/**
	 * @param int $listMode
	 *
	 * @return Config
	 */
	public function setListMode($listMode)
	{
		$this->listMode = $listMode;

		return $this;
	}
}
