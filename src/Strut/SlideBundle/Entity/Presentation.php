<?php

namespace Strut\SlideBundle\Entity;

class Presentation {

	/**
	 * @var Slide[]
	 */
	private $slides;

	/** @var array */
	private $customBackgrounds;

	/** @var bool */
	private $exists;

	/** @var string */
	private $background;

	/** @var Slide */
	private $activeSlide;

	/** @var string */
	private $fileName;

	/** @var string */
	private $surface;

	/** @var string */
	private $customStylesheet;

	/** @var string */
	private $deckVersion;

	/** @var int */
	private $overviewX = 0;

	/** @var int */
	private $overviewY;

	/**
	 * @return Slide[]
	 */
	public function getSlides(): array
	{
		return $this->slides;
	}

	/**
	 * @param Slide[] $slides
	 */
	public function setSlides(array $slides)
	{
		$this->slides = $slides;
	}

	/**
	 * @return array
	 */
	public function getCustomBackgrounds(): array
	{
		return $this->customBackgrounds;
	}

	/**
	 * @param array $customBackgrounds
	 */
	public function setCustomBackgrounds(array $customBackgrounds)
	{
		$this->customBackgrounds = $customBackgrounds;
	}

	/**
	 * @return bool
	 */
	public function isExists(): bool
	{
		return $this->exists;
	}

	/**
	 * @param bool $exists
	 */
	public function setExists(bool $exists)
	{
		$this->exists = $exists;
	}

	/**
	 * @return Slide
	 */
	public function getActiveSlide(): Slide
	{
		return $this->activeSlide;
	}

	/**
	 * @param Slide $activeSlide
	 */
	public function setActiveSlide(Slide $activeSlide)
	{
		$this->activeSlide = $activeSlide;
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return $this->fileName;
	}

	/**
	 * @param string $fileName
	 */
	public function setFileName(string $fileName)
	{
		$this->fileName = $fileName;
	}

	/**
	 * @return string
	 */
	public function getDeckVersion(): string
	{
		return $this->deckVersion;
	}

	/**
	 * @param string $deckVersion
	 */
	public function setDeckVersion(string $deckVersion)
	{
		$this->deckVersion = $deckVersion;
	}

	public function getOverviewX()
	{
		return $this->overviewX !== null ?: 0 ;
	}

	public function setOverviewX($overviewX = 0)
	{
		$this->overviewX = $overviewX;
	}

	public function getOverviewY()
	{
		return $this->overviewY !== null ?: 0 ;
	}

	public function setOverviewY($overviewY = 0)
	{
		$this->overviewY = $overviewY;
	}

	public function toArray() {
		return get_object_vars($this);
	}

	public static function object_to_array($obj) {
		if(is_object($obj)) {
			if ($obj instanceof Slide || $obj instanceof Component) {
				$obj = $obj->toArray();
			} else {
				$obj = get_object_vars($obj);
			}
		}
		if(is_array($obj)) {
			$new = array();
			foreach($obj as $key => $val) {
				$new[$key] = Presentation::object_to_array($val);
			}
		}
		else $new = $obj;
		return $new;
	}

	/**
	 * @return mixed
	 */
	public function getCustomStylesheet()
	{
		return $this->customStylesheet;
	}

	/**
	 * @param mixed $customStylesheet
	 */
	public function setCustomStylesheet($customStylesheet)
	{
		$this->customStylesheet = $customStylesheet;
	}

	/**
	 * @return string
	 */
	public function getBackground()
	{
		return $this->background;
	}

	/**
	 * @param string $background
	 */
	public function setBackground($background)
	{
		$this->background = $background;
	}

	/**
	 * @return string
	 */
	public function getSurface()
	{
		return $this->surface;
	}

	/**
	 * @param string $surface
	 */
	public function setSurface($surface)
	{
		$this->surface = $surface;
	}
}
