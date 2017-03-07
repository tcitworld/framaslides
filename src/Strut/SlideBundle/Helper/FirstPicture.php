<?php

namespace Strut\SlideBundle\Helper;

use Strut\SlideBundle\Entity\Components\Image;
use Strut\StrutBundle\Entity\Presentation;

class FirstPicture {

	private $presentationMapper;

	public function __construct(PresentationMapper $presentationMapper)
	{
		$this->presentationMapper = $presentationMapper;
	}

	public function getFirstPicture(Presentation $presentation): string
	{
		/** @var \Strut\SlideBundle\Entity\Presentation $presentationEntity */
		$presentationEntity = $this->presentationMapper->setPresentation($presentation)->mapper();

		foreach ($presentationEntity->getSlides() as $slide) {
			foreach ($slide->getComponents() as $component) {
				if ($component instanceof Image) {
					return $component->getSrc();
				}
			}
		}
		return false;
	}
}