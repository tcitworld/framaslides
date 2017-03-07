<?php

namespace Strut\SlideBundle\Entity\Components;

use Strut\SlideBundle\Entity\Component;

class Shape extends Component
{

	/** @var string */
	private $fill;

	/** @var string */
	private $markup;

	public function __construct($component)
	{
		parent::__construct($component);

		$this->fill = $component->fill;

		$this->markup = $component->markup;
	}

	/**
	 * @return mixed
	 */
	public function getFill()
	{
		return $this->fill;
	}

	/**
	 * @param mixed $fill
	 */
	public function setFill($fill)
	{
		$this->fill = $fill;
	}

	/**
	 * @return mixed
	 */
	public function getMarkup()
	{
		return $this->markup;
	}

	/**
	 * @param mixed $markup
	 */
	public function setMarkup($markup)
	{
		$this->markup = $markup;
	}
}