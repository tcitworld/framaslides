<?php

namespace Strut\SlideBundle\Entity;

use Strut\SlideBundle\Entity\Components\Image;
use Strut\SlideBundle\Entity\Components\Shape;
use Strut\SlideBundle\Entity\Components\TextBox;
use Strut\SlideBundle\Entity\Components\Video;
use Strut\SlideBundle\Entity\Components\WebFrame;

class ComponentFactory {

	static public function chooseType($type, $component) {
		switch ($type) {
			case 'TextBox':
				return new TextBox($component);
			case 'Shape':
				return new Shape($component);
			case 'Video':
				return new Video($component);
			case 'Image':
				return new Image($component);
			case 'WebFrame':
				return new WebFrame($component);
			default:
				return new Component($component);
		}
	}

}