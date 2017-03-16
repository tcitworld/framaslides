<?php

namespace Tests\Strut\StrutBundle\Controller;

use Tests\Strut\StrutBundle\StrutTestCase;

class DefaultControllerTest extends StrutTestCase
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

	public function testShowPresentationSettings()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser());

		$client->request('GET', '/template/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertContains('Modifier la présentation ' . $content->getTitle(), $client->getResponse()->getContent());
	}
}
