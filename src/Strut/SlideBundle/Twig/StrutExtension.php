<?php

namespace Strut\SlideBundle\Twig;

use HTMLPurifier;
use HTMLPurifier_Config;
use Strut\SlideBundle\Entity\Components\Shape;
use Strut\SlideBundle\Entity\Presentation;
use Strut\SlideBundle\Entity\Slide;
use Twig_Extension;

class StrutExtension extends Twig_Extension {

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter('toDeg', [$this, 'toDeg']),
			new \Twig_SimpleFilter('checkxss', [$this, 'checkxss']),
			new \Twig_SimpleFilter('purifier', [$this, 'purifier'])
		];
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('isBGClass', [$this, 'isBGClass']),
			new \Twig_SimpleFunction('isBGImg', [$this, 'isBGImg']),
			new \Twig_SimpleFunction('getBGImgStyle', [$this, 'getBGImgStyle']),
			new \Twig_SimpleFunction('determineSurface', [$this, 'determineSurface']),
			new \Twig_SimpleFunction('determineBG', [$this, 'determineBG']),
			new \Twig_SimpleFunction('adaptSVG', [$this, 'adaptSVG']),

		);
	}

	public function toDeg(float $angle): float
	{
		return $angle * 180 / pi();
	}

	public function isBGClass($class): bool
	{
		return $class && !substr($class, 0, 4) == 'img:';
	}

	public function isBGImg($img): bool
	{
		return $img && substr($img, 0, 4) == 'img:';
	}

	public function getBGImgStyle($style): string
	{
		return 'background-image: url('. substr($style, 4) . ');';
	}

	public function determineSurface(Slide $slide, Presentation $presentation): string
	{
		$result = '';
		if ($slide) {
			$result = $slide->getSurface();
			if ($result === 'bg-default' || $result == null) {
				$result = $presentation->getSurface();
			}
		}

		if ($result == null) {
			$result = $presentation->getSurface() || 'bg-default';
		}

		if ($result && $result != 'bg-default' && strstr($result, 'img:') === false) {
			return ' ' . $result . ' ';
		}
		return '';
	}

	public function determineBG(Slide $slide, Presentation $presentation): string
	{

		$surface = $this->determineSurface($slide, $presentation);
		if ($slide) {
			$result = $slide->getBackground();
			if ($result == 'bg-default' || $result == null) {
				$result = $presentation->getBackground() || 'bg-transparent';
			}

			if ($result == 'bg-transparent') {
				$result = $surface;
			}
		} else {
			$result = $presentation->getBackground() || 'bg-default';
		}

		// if ($result == 'bg-default') {
		//	$result = $surface;
		// }

		if ($result && substr($result, 0, 4) == 'img:') {
			return '';
		}
		return $result;
	}

	public function adaptSVG(Shape $shape): string
	{
		$attr_insert = '';
		$retval = '';
		if ($shape->getMarkup()) {
			if ($shape->getFill()) {
				$attr_insert .= ' fill="' . $shape->getFill() . '" ';
			}
			if ($shape->getScale()) {
				$attr_insert .= ' height="' . $shape->getScale()->height . '" ';
				$attr_insert .= ' width="'  . $shape->getScale()->width  . '" ';
			}
			$retval = str_replace('<svg ', '<svg ' . $attr_insert, $shape->getMarkup());
		}
		return $retval;
	}

	public function checkxss(string $url): string
	{
		if (substr($url, 0, 4) == 'http') {
			return $url;
		} else {
			return '';
		}
	}

	public function purifier(string $text): string
	{
		$elements = 'p,br,strong,em,strike,ol,ul,li,h1,h2,h3,dl,dd,dt,pre,code,q,blockquote,abbr,cite,table,thead,tbody,th,tr,td,a[href|target|rel|id],img[src|title|alt|width|height|style]';

		$schemes = [
			'http' => true,
			'https' => true,
			'mailto' => true
		];
		$config = HTMLPurifier_Config::createDefault();
		$config->set('URI.AllowedSchemes', $schemes);
		$config->set('HTML.Allowed', $elements);
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($text);
	}
}