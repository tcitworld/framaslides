<?php

namespace Strut\SlideBundle\Controller;

use Handlebars\Handlebars;
use Strut\SlideBundle\Entity\Slide;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
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
	 * @Route("preview/version/{version}", name="slide-version-preview")
	 */
	public function previewSlidesVersionAction(Version $version): Response
	{
		// TODO : check that user can access

		/** @var \Strut\SlideBundle\Entity\Presentation $presentationEntity */
		$presentationEntity = $this->get('strut.slides.mapper')
			->setVersion($version)
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

	/**
	 * @Route("preview/{presentation}/notes", name="slide-notes")
	 */
	public function previewSlidesNotesAction(Presentation $presentation): Response
	{
		/** @var \Strut\SlideBundle\Entity\Presentation $presentationEntity */
		$presentationEntity = $this->get('strut.slides.mapper')
			->setPresentation($presentation)
			->mapper();

		return $this->render('default/slides/handouts.html.twig', [
			'presentation' => $presentationEntity,
		]);
	}
}