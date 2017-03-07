<?php

namespace Strut\SlideBundle\Controller;

use Handlebars\Handlebars;
use Strut\SlideBundle\Entity\Slide;
use Strut\StrutBundle\Entity\Presentation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use TCPDF;

class SlidePreview extends Controller {

	/**
	 * @Route("preview/{presentation}", name="slide-preview")
	 */
	public function previewSlidesAction(Presentation $presentation): Response
	{
		/** @var \Strut\SlideBundle\Entity\Presentation $presentationEntity */
		$presentationEntity = $this->get('strut.slides.mapper')
			->setPresentation($presentation)
			->mapper();

		return $this->render('default/slides/render.html.twig', [
			'presentation' => $presentationEntity,
		]);

	}

	/**
	 * @Route("preview/{presentation}/debug", name="slide-preview-debug")
	 */
	public function previewSlidesDebugAction(Presentation $presentation)
	{
		$presentationEntity = $this->get('strut.slides.mapper')
			->setPresentation($presentation)
			->mapper();

		var_dump($presentationEntity);
	}
}