<?php

namespace Strut\SlideBundle\Entity\Components;

use HTMLPurifier;
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

		$this->setSrc($component->src);
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
		$purifier = new HTMLPurifier();
		$this->src = $purifier->purify($src);
	}
}