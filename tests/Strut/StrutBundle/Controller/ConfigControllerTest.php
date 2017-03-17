<?php

namespace Tests\Strut\StrutBundle\Controller;

use Tests\Strut\StrutBundle\StrutTestCase;

class ConfigControllerTest extends StrutTestCase
{
	public function testIndex()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$crawler = $client->request('GET', '/config');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$this->assertCount(1, $crawler->filter('button[id=config_save]'));
		$this->assertCount(1, $crawler->filter('button[id=change_passwd_save]'));
		$this->assertCount(1, $crawler->filter('button[id=update_user_save]'));
	}

	public function testUpdate()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$crawler = $client->request('GET', '/config');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$form = $crawler->filter('button[id=config_save]')->form();

		$data = [
			'config[language]' => 'en',
		];

		$client->submit($form, $data);

		$this->assertEquals(302, $client->getResponse()->getStatusCode());

		$crawler = $client->followRedirect();

		$this->assertContains('The configuration was saved', $crawler->filter('body')->extract(['_text'])[0]);
	}
}