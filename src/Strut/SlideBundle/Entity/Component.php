<?php

namespace Strut\SlideBundle\Entity;

use Strut\SlideBundle\Entity\Scale;

class Component
{
	private $x;
	private $y;
	private $z = 0;

	private $selected;

	/** @var Scale */
	// For some reason $scale is stdClass and not Scale
	private $scale;

	/** @var float */
	private $rotate;

	/** @var float */
	private $skewX;

	/** @var float */
	private $skewY;

	/** @var string */
	private $type;

	public function __construct($component)
	{
		$this->x = $component->x;
		$this->y = $component->y;
		if (isset($component->z)) {
			$this->z = $component->z;
		}

		$this->selected = $component->selected;

		$this->scale = $component->scale;

		$this->type = $component->type;

		if (isset($component->rotate)) {
			$this->rotate = $component->rotate;
		}

		if (isset($component->skewX)) {
			$this->skewX = $component->skewX;
		}

		if (isset($component->skewY)) {
			$this->skewY = $component->skewY;
		}

	}

	/**
	 * @return mixed
	 */
	public function getX()
	{
		return $this->x;
	}

	/**
	 * @param mixed $x
	 */
	public function setX($x)
	{
		$this->x = $x;
	}

	/**
	 * @return mixed
	 */
	public function getY()
	{
		return $this->y;
	}

	/**
	 * @param mixed $y
	 */
	public function setY($y)
	{
		$this->y = $y;
	}

	/**
	 * @return mixed
	 */
	public function getZ()
	{
		return $this->z;
	}

	/**
	 * @param mixed $z
	 */
	public function setZ($z)
	{
		$this->z = $z;
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
	 * @return Scale
	 */
	public function getScale()
	{
		return $this->scale;
	}

	/**
	 * @param Scale $scale
	 */
	public function setScale(Scale $scale)
	{
		$this->scale = $scale;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type)
	{
		$this->type = $type;
	}

	public function toArray() {
		return get_object_vars($this);
	}

	/**
	 * @return float
	 */
	public function getRotate()
	{
		return $this->rotate;
	}

	/**
	 * @param float $rotate
	 */
	public function setRotate(float $rotate)
	{
		$this->rotate = $rotate;
	}

	/**
	 * @return float
	 */
	public function getSkewX()
	{
		return $this->skewX;
	}

	/**
	 * @param float $skewX
	 */
	public function setSkewX(float $skewX)
	{
		$this->skewX = $skewX;
	}

	/**
	 * @return float
	 */
	public function getSkewY()
	{
		return $this->skewY;
	}

	/**
	 * @param float $skewY
	 */
	public function setSkewY(float $skewY)
	{
		$this->skewY = $skewY;
	}

}
