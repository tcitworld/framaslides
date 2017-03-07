<?php

namespace Strut\SlideBundle\Helper;

use Strut\SlideBundle\Entity\Components\Image;
use Strut\SlideBundle\Entity\Components\Shape;
use Strut\SlideBundle\Entity\Components\TextBox;
use Strut\SlideBundle\Entity\Components\Video;
use Strut\SlideBundle\Entity\Components\WebFrame;
use Strut\SlideBundle\Entity\Presentation;
use Strut\SlideBundle\Entity\Stats;
use Strut\StrutBundle\Entity\Presentation as Prez;

class ProvideStats {

	private $presentationMapper;

	public function __construct(PresentationMapper $presentationMapper)
	{
		$this->presentationMapper = $presentationMapper;
	}

	/**
	 * @param Presentation $presentation
	 * @return Stats
	 */
	public function makeStats($presentation)
	{
		if (!$presentation instanceof Presentation) {
			if ($presentation instanceof Prez) {
				$presentation = $this->presentationMapper->setPresentation($presentation)->mapper();
			} else {
				return null;
			}
		}

		$stats = new Stats();

		foreach ($presentation->getSlides() as $slide)
		{
			foreach ($slide->getComponents() as $component) {
				if ($component instanceof Image) {
					$stats->increaseNbImages();
				} elseif ($component instanceof Video) {
					$stats->increaseNbVideos();
				} elseif ($component instanceof WebFrame) {
					$stats->increaseNbFrames();
				} elseif ($component instanceof TextBox) {
					$stats->increaseNbTextAreas();
				} elseif ($component instanceof Shape) {
					$stats->increaseNbShapes();
				}
			}
		}
		return $stats;
	}
}