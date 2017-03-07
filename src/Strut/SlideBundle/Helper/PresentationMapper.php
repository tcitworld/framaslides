<?php

namespace Strut\SlideBundle\Helper;

use JsonMapper;
use Psr\Log\LoggerInterface;
use Strut\SlideBundle\Entity\Presentation as PresentationEntity;
use Strut\StrutBundle\Entity\Presentation;

class PresentationMapper {

	/** @var Presentation */
	private $presentation;

	/** @var LoggerInterface */
	private $logger;


	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function setPresentation(Presentation $presentation): PresentationMapper
	{
		$this->presentation = $presentation;
		return $this;
	}

	public function mapper()
	{
		$presentationJson = json_decode($this->presentation->getLastVersion()->getContent());

		$mapper = new JsonMapper();

		$mapper->setLogger($this->logger);
		$mapper->classMap['scale'] = 'Scale';
		$this->logger->info('Mapping presentation ' . $this->presentation->getId() . ' to classes');
		$presentationEntity = $mapper->map($presentationJson, new PresentationEntity());

		return $presentationEntity;
	}
}