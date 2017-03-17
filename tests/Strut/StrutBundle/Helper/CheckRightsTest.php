<?php
/**
 * Created by PhpStorm.
 * User: tcit
 * Date: 16/03/17
 * Time: 15:59
 */

namespace Tests\Strut\StrutBundle\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Strut\GroupBundle\Entity\Group;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Helper\CheckRights;
use Strut\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tests\Strut\StrutBundle\StrutTestCase;

class CheckRightsTest extends StrutTestCase
{
	public function testRightsForSameUser()
	{
		$logHandler = new TestHandler();
		$logger = new Logger('test', [$logHandler]);

		$checkRights = new CheckRights($logger);

		$this->logInAs('bob');

		$presentation = new Presentation($this->getLoggedInUser());

		$checkRights->checkUserPresentationAction($this->getLoggedInUser(), $presentation);
	}

	public function testRightsForDifferentUser()
	{
		$logHandler = new TestHandler();
		$logger = new Logger('test', [$logHandler]);

		$checkRights = new CheckRights($logger);

		$bob = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'bob']);

		$presentation = new Presentation($bob);

		$this->expectException(AccessDeniedException::class);

		$admin = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'admin']);

		$checkRights->checkUserPresentationAction($admin, $presentation);
	}

	public function testRightsForPresentationSharedWithGroupWithUserIn()
	{
		$logHandler = new TestHandler();
		$logger = new Logger('test', [$logHandler]);

		$checkRights = new CheckRights($logger);

		$group = new Group('group');

		/** @var User $bob */
		$bob = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'bob']);

		$bob->addAGroup($group, Group::ROLE_ADMIN);

		$presentation = new Presentation($bob);

		$presentation->addGroupShare($group);

		/** @var User $admin */
		$admin = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'admin']);

		$admin->addAGroup($group, Group::ROLE_ADMIN);

		$checkRights->checkUserPresentationAction($admin, $presentation);

		$admin->removeGroup($group);
	}

	public function testRightsForPresentationSharedWithGroupWithUserOut()
	{
		$logHandler = new TestHandler();
		$logger = new Logger('test', [$logHandler]);

		$checkRights = new CheckRights($logger);

		$group = new Group('group2');

		/** @var User $bob */
		$bob = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'bob']);

		$bob->addAGroup($group, Group::ROLE_ADMIN);

		$presentation = new Presentation($bob);

		$presentation->addGroupShare($group);

		$this->expectException(AccessDeniedException::class);

		/** @var User $admin */
		$admin = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'admin']);

		$checkRights->checkUserPresentationAction($admin, $presentation);

		$bob->removeGroup($group);
	}
}
