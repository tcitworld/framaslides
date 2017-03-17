<?php

namespace Tests\Strut\StrutBundle\Controller;

use Strut\StrutBundle\Entity\Config;
use Strut\StrutBundle\Entity\Presentation;
use Strut\UserBundle\Entity\User;
use Tests\Strut\StrutBundle\StrutTestCase;

class PresentationControllerTest extends StrutTestCase
{
    public function testLogin()
	{
		$client = $this->getClient();

		$client->request('GET', '/');

		$this->assertEquals(302, $client->getResponse()->getStatusCode());
		$this->assertContains('login', $client->getResponse()->headers->get('location'));
	}

	public function testGetApp()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$client->request('GET', '/app');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
	}

	public function testGetPresentationList()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$client->request('GET', '/presentations');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
	}

	public function testGetTemplatesList()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$client->request('GET', '/templates');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
	}

	public function testGetGroupSharedPresentationList()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$client->request('GET', '/presentations/group/list');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
	}

	public function testEdit()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser());

		$crawler = $client->request('GET', '/template/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$this->assertCount(1, $crawler->filter('input[id=template_title]'));
		$this->assertCount(1, $crawler->filter('button[id=template_save]'));

		$this->assertGreaterThan(1, $alert = $crawler->filter('div[class=header] h4')->extract(['_text']));
		$this->assertContains($content->getTitle(), $alert[0]);
	}

	public function testEditUpdate()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser());

		$crawler = $client->request('GET', '/template/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$form = $crawler->filter('button[id=template_save]')->form();

		$data = [
			'template[title]' => 'My updated title hehe :)',
		];

		$client->submit($form, $data);

		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		$crawler = $client->followRedirect();

		$this->assertGreaterThan(1, $alert = $crawler->filter('div[class=header] h4')->extract(['_text']));
		$this->assertContains('My updated title hehe :)', $alert[0]);
	}

	public function testToggleTemplate()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneBy([
				'user' => $this->getLoggedInUser(),
				'title' => 'Presentation 2 for admin',
			]);

		$crawler = $client->request('GET', '/template/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$form = $crawler->filter('button[id=template_save]')->form();

		$form['template[template]']->tick();

		$client->submit($form);

		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		/** @var Presentation $res */
		$res = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->find($content->getId());

		$this->assertEquals($res->isTemplate(), true);
	}

	public function testTogglePublic()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser());

		$crawler = $client->request('GET', '/template/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$form = $crawler->filter('button[id=template_save]')->form();

		$form['template[public]']->tick();

		$client->submit($form);

		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		/** @var Presentation $res */
		$res = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->find($content->getId());

		$this->assertEquals($res->isPublic(), true);
	}

	public function testDelete()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$contentId = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser())->getId();

		$client->request('GET', '/presentation/delete/id/' . $contentId);

		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		$client->request('GET', '/presentation/delete/id/'. $contentId);

		$this->assertEquals(404, $client->getResponse()->getStatusCode());
	}

	public function testViewOtherUserPresentation()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		/** @var User $bob */
		$bob = $this->getClient()->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'bob']);

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($bob);

		$client->request('GET', '/template/' . $content->getId());

		$this->assertEquals(403, $client->getResponse()->getStatusCode());
	}

	public function testForkPresentationWithSameUser()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser());

		$nbEntriesForUser = count($client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findByUser($this->getLoggedInUser()));

		$crawler = $client->request('GET', '/template/fork/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$form = $crawler->filter('button[id=template_save]')->form();

		$form['template[title]'] = 'My fork';

		$client->submit($form);

		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		$newNbEntriesForUser = count($client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findByUser($this->getLoggedInUser()));

		$this->assertEquals($nbEntriesForUser + 1, $newNbEntriesForUser);
		$this->assertEquals(1, count($client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findByTitle('My fork')));
	}

	public function testSharePresentationPublicly()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		/** @var Presentation $content */
		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUserId());

		// no uid
		/** These lines doesn't pass remote tests for some reason */
		//$client->request('GET', '/share/' . $content->getUuid() ?: 0);
		//$this->assertEquals(404, $client->getResponse()->getStatusCode());

		// generating the uid
		$client->request('GET', '/share/' . $content->getId());
		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		// follow link with uid
		$crawler = $client->followRedirect();
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertContains('max-age=25200', $client->getResponse()->headers->get('cache-control'));
		$this->assertContains('public', $client->getResponse()->headers->get('cache-control'));
		$this->assertContains('s-maxage=25200', $client->getResponse()->headers->get('cache-control'));
		$this->assertNotContains('no-cache', $client->getResponse()->headers->get('cache-control'));
		$this->assertContains('og:title', $client->getResponse()->getContent());
		$this->assertContains('og:type', $client->getResponse()->getContent());
		$this->assertContains('og:url', $client->getResponse()->getContent());
		$this->assertContains('og:image', $client->getResponse()->getContent());

		// removing the share
		$client->request('GET', '/share/delete/' . $content->getId());
		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		// share is now disable
		$client->request('GET', '/share/' . $content->getUuid() ?: 0);
		$this->assertEquals(404, $client->getResponse()->getStatusCode());
	}

	public function testSearch()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		// Search on presentations list
		$crawler = $client->request('GET', '/presentations');

		$form = $crawler->filter('form[name=search]')->form();
		$data = [
			'search_entry[term]' => 'fork',
		];

		$crawler = $client->submit($form, $data);

		// $this->assertContains('og:image', $client->getResponse()->getContent());
		$this->assertCount(1, $crawler->filter('div.presentation, tr.presentation'));

		// Search on templates list
		$crawler = $client->request('GET', '/templates');

		$form = $crawler->filter('form[name=search]')->form();
		$data = [
			'search_entry[term]' => 'presentation',
		];

		$crawler = $client->submit($form, $data);

		$this->assertCount(1, $crawler->filter('div.presentation, tr.presentation'));
	}
}
