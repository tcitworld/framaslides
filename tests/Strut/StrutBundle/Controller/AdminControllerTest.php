<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Strut\StrutBundle\StrutTestCase;


class AdminControllerTest extends StrutTestCase
{
    public function testSettingsWithAdmin()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSettingsWithNormalUser()
    {
        $this->logInAs('bob');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/users');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
