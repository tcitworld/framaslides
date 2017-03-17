<?php

namespace Strut\SlideBundle\Controller;

use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

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
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $version->getPresentation());

		/** @var \Strut\SlideBundle\Entity\Presentation $presentationEntity */
		$presentationEntity = $this->get('strut.slides.mapper')
			->setVersion($version)
			->mapper();

		return $this->render('default/slides/render.html.twig', [
			'presentation' => $presentationEntity,
		]);
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
