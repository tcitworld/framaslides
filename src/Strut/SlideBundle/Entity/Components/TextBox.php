<?php

namespace Strut\SlideBundle\Entity\Components;

use Strut\SlideBundle\Entity\Component;

class TextBox extends Component
{

	private $text;
	private $size;
	private $TextBox;

	public function __construct($component)
	{
		parent::__construct($component);
		$this->text = $component->text;
		$this->size = $component->size;

		$this->TextBox = $component->TextBox;
	}

	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param mixed $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return mixed
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param mixed $size
	 */
	public function setSize($size)
	{
		$this->size = $size;
	}

	/**
	 * @return mixed
	 */
	public function getTextBox()
	{
		return $this->TextBox;
	}

	/**
	 * @param mixed $TextBox
	 */
	public function setTextBox($TextBox)
	{
		$this->TextBox = $TextBox;
	}

	public function toArray() {
		return get_object_vars($this);
	}
}
