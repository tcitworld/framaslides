<?php

namespace Strut\SlideBundle\Entity\Components;

use Strut\SlideBundle\Entity\Component;

class Image extends Component
{

	/** @var string */
	private $imageType;

	/** @var string */
	private $src;

	function __construct($component)
	{
		parent::__construct($component);
		$this->imageType = $component->imageType;
		$this->src = $component->src;
	}

	/**
	 * @return string
	 */
	public function getImageType(): string
	{
		return $this->imageType;
	}

	/**
	 * @param string $imageType
	 */
	public function setImageType(string $imageType)
	{
		$this->imageType = $imageType;
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
}