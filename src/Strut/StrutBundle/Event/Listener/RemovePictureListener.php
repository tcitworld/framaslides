<?php

namespace Strut\StrutBundle\Event\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Strut\StrutBundle\Entity\Picture;

class RemovePictureListener implements EventSubscriber
{

	protected $path;

	protected $logger;

	public function __construct($path, LoggerInterface $logger)
	{
		$this->path = $path;
		$this->logger = $logger;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return ['postRemove'];
	}

	/**
	 * @param LifecycleEventArgs $args
	 */
	public function postRemove(LifecycleEventArgs $args)
	{
		/** @var Picture $picture */
		$picture = $args->getEntity();

		if (!$picture instanceof Picture) {
			return;
		}

		$this->logger->info('Deleting picture file for picture named ' . $picture->getFileName());

		$file = $this->path . '/' . $picture->getUuid() . '.' . $picture->getExtension();
		if(file_exists($file) && $file) {
			unlink($file);
			$this->logger->info('Deleted file at ' . $file);
		} else {
			$this->logger->warning('File not found : ' . $file);
		}
	}
}