<?php

namespace Strut\SlideBundle\Entity\Components;

use Strut\SlideBundle\Entity\Component;

class WebFrame extends Component
{
	/**
	 * @var string
	 */
	private $src;

	public function __construct($component)
	{
		parent::__construct($component);

		$this->src = $component->src;
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