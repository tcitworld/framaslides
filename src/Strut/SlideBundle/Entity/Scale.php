<?php

namespace Strut\SlideBundle\Entity;

class Scale {

	private $x;

	private $y;

	private $width;

	private $height;

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
	 * @return float
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param float $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return float
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param float $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}
}