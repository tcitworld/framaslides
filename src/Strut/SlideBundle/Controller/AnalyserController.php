<?php

namespace Strut\SlideBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Presentation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AnalyserController extends Controller {


	/**
	 * @Route("/{presentation}", name="analyse")
	 *
	 */
	public function analyseAction(Presentation $presentation): Response
	{
		$presentationEntity = $this->get('strut.slides.mapper')
			->setPresentation($presentation)
			->mapper();

		$stats = $this->get('strut.slides.stats')->makeStats($presentationEntity);

		return $this->render('default/slides/analytics.html.twig', [
			'presentation' => $presentationEntity,
			'stats' => $stats
		]);

	}
}
