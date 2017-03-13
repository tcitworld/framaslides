<?php

namespace Strut\SlideBundle\Entity\Components;

use Strut\SlideBundle\Entity\Component;

class Video extends Component
{

	/** @var string */
	private $videoType;

	/** @var string */
	private $src;

	/** @var string */
	private $shortSrc;

	/** @var string */
	private $srcType;

	public function __construct($component)
	{
		parent::__construct($component);

		$this->videoType = $component->videoType;

		$this->src = $component->src;

		$this->shortSrc = $component->shortSrc;

		$this->srcType = $component->srcType;
	}

	/**
	 * @return string
	 */
	public function getVideoType(): string
	{
		return $this->videoType;
	}

	/**
	 * @param string $videoType
	 */
	public function setVideoType(string $videoType)
	{
		$this->videoType = $videoType;
	}

	/**
	 * @return string
	 */
	public function getSrc(): string
	{
		return $this->src;
	}

	/**
	 * @param string $src
	 */
	public function setSrc(string $src)
	{
		$this->src = $src;
	}

	/**
	 * @return string
	 */
	public function getShortSrc(): string
	{
		return $this->shortSrc;
	}

	/**
	 * @param string $shortSrc
	 */
	public function setShortSrc(string $shortSrc)
	{
		$this->shortSrc = $shortSrc;
	}

	/**
	 * @return string
	 */
	public function getSrcType(): string
	{
		return $this->srcType;
	}

	/**
	 * @param string $srcType
	 */
	public function setSrcType(string $srcType)
	{
		$this->srcType = $srcType;
	}
}
