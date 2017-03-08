<?php

namespace Strut\SlideBundle\Helper;

use JsonMapper;
use Psr\Log\LoggerInterface;
use Strut\SlideBundle\Entity\Presentation as PresentationEntity;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;

class PresentationMapper {

	/** @var string */
	private $content;

	/** @var LoggerInterface */
	private $logger;


	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function setPresentation(Presentation $presentation): PresentationMapper
	{
		$this->content = $presentation->getLastVersion()->getContent();
		return $this;
	}

	public function setVersion(Version $version): PresentationMapper
	{
		$this->content = $version->getContent();
		return $this;
	}

	public function mapper()
	{
		$presentationJson = json_decode($this->content);

		$mapper = new JsonMapper();

		$mapper->setLogger($this->logger);
		$mapper->classMap['scale'] = 'Scale';
		$this->logger->info('Mapping to classes');
		$presentationEntity = $mapper->map($presentationJson, new PresentationEntity());

		return $presentationEntity;
	}
}