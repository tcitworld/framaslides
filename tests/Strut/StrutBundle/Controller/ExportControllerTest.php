<?php

namespace Tests\Strut\StrutBundle\Controller;

use Patchwork\Utf8;
use Strut\StrutBundle\Entity\Presentation;
use Tests\Strut\StrutBundle\StrutTestCase;

class ExportControllerTest extends StrutTestCase
{
	public function testExport()
	{
		$client = $this->getClient();

		$client->request('GET', '/presentation/export/1');

		$this->assertEquals(302, $client->getResponse()->getStatusCode());
		$this->assertContains('login', $client->getResponse()->headers->get('location'));
	}

	public function testBadEntryId()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		$client->request('GET', '/presentation/export/0');

		$this->assertEquals(404, $client->getResponse()->getStatusCode());
	}

	public function testJsonExport()
	{
		$this->logInAs('admin');
		$client = $this->getClient();

		/** @var Presentation $content */
		$content = $client->getContainer()
			->get('doctrine.orm.entity_manager')
			->getRepository('Strut\StrutBundle\Entity\Presentation')
			->findOneByUser($this->getLoggedInUser());

		$client->request('GET', '/presentation/export/' . $content->getId());

		$this->assertEquals(200, $client->getResponse()->getStatusCode());

		$headers = $client->getResponse()->headers;
		$this->assertEquals('application/json', $headers->get('content-type'));
		$this->assertEquals('attachment; filename="' . Utf8::toAscii($content->getTitle()) . '.json' . '"', $headers->get('content-disposition'));
	}
}