<?php

namespace Strut\StrutBundle\Helper;

use Psr\Log\LoggerInterface;
use Strut\StrutBundle\Entity\Presentation;
use Strut\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CheckRights {

	private $logger;

	function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Check if the logged user can manage the given entry.
	 *
	 * @param Presentation $presentation
	 * @return bool
	 */
	public function checkUserPresentationAction(User $user, Presentation $presentation)
	{
		if ($user->getId() != $presentation->getUser()->getId() && $presentation->getGroupShares()->isEmpty()) {
			$this->logger->info('user ' . $user->getUsername() . ' has no rights on presentation ' . $presentation->getTitle() . ' which belongs to ' . $presentation->getUser()->getUsername());
			throw new AccessDeniedException("You don't have the rights to access this presentation.");
		}

		if (!$presentation->getGroupShares()->isEmpty() && empty(array_intersect($user->getGroups()->toArray(), $presentation->getGroupShares()->toArray()))) {
			$this->logger->info('user ' . $user->getUsername() . ' is not in one of the groups for presentation ' . $presentation->getTitle());
			throw new AccessDeniedException("You are not in the group to access this presentation");
		}
		return true;
	}
}