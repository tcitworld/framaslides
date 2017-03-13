<?php

namespace Strut\SlideBundle\Entity;

class Slide {

	private $components;

	/** @var int */
	private $impScale;

	/** @var int */
	private $rotateX;

	/** @var int */
	private $rotateY;

	/** @var int */
	private $rotateZ;

	/** @var int */
	private $index;

	/** @var bool */
	private $selected;

	/** @var bool */
	private $active;

	/** @var int */
	private $x;

	/** @var int */
	private $y;

	/** @var int */
	private $z;

	/** @var string */
	private $background;

	/** @var string */
	private $surface;

	/** @var string */
	private $markdown;

	public function getComponents(): array
	{
		return $this->components;
	}

	public function setComponents(array $components)
	{
		$componentsObj = [];
		foreach ($components as $component) {
			$componentsObj[] = ComponentFactory::chooseType($component->type, $component);
		}
		$this->components = $componentsObj;
	}

	/**
	 * @return mixed
	 */
	public function getImpScale()
	{
		return $this->impScale;
	}

	/**
	 * @param mixed $impScale
	 */
	public function setImpScale($impScale)
	{
		$this->impScale = $impScale;
	}

	/**
	 * @return int
	 */
	public function getRotateX(): int
	{
		return $this->rotateX;
	}

	/**
	 * @param int $rotateX
	 */
	public function setRotateX(int $rotateX)
	{
		$this->rotateX = $rotateX;
	}

	/**
	 * @return int
	 */
	public function getRotateY(): int
	{
		return $this->rotateY;
	}

	/**
	 * @param int $rotateY
	 */
	public function setRotateY(int $rotateY)
	{
		$this->rotateY = $rotateY;
	}

	/**
	 * @return int
	 */
	public function getRotateZ(): int
	{
		return $this->rotateZ;
	}

	/**
	 * @param int $rotateZ
	 */
	public function setRotateZ(int $rotateZ)
	{
		$this->rotateZ = $rotateZ;
	}

	/**
	 * @return mixed
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * @param mixed $index
	 */
	public function setIndex($index)
	{
		$this->index = $index;
	}

	/**
	 * @return mixed
	 */
	public function getSelected()
	{
		return $this->selected;
	}

	/**
	 * @param mixed $selected
	 */
	public function setSelected($selected)
	{
		$this->selected = $selected;
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 */
	public function setActive($active)
	{
		$this->active = $active;
	}

	/**
	 * @return int
	 */
	public function getX(): int
	{
		return $this->x;
	}

	/**
	 * @param int $x
	 */
	public function setX(int $x)
	{
		$this->x = $x;
	}

	public function scaleX(): float
	{
		return $this->x * 1024 / 75;
	}

	/**
	 * @return int
	 */
	public function getY(): int
	{
		return $this->y;
	}

	/**
	 * @param int $y
	 */
	public function setY(int $y)
	{
		$this->y = $y;
	}

	public function scaleY(): float
	{
		return $this->y * 768 / 50;
	}

	/**
	 * @return int
	 */
	public function getZ(): int
	{
		return $this->z;
	}

	/**
	 * @param int $z
	 */
	public function setZ(int $z)
	{
		$this->z = $z;
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
	public function setBackground(string $background)
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
	public function setSurface(string $surface)
	{
		$this->surface = $surface;
	}

	public function toArray() {
		return get_object_vars($this);
	}

	/**
	 * @return string
	 */
	public function getMarkdown()
	{
		return $this->markdown;
	}

	/**
	 * @param string $markdown
	 */
	public function setMarkdown($markdown)
	{
		$this->markdown = $markdown;
	}
}
