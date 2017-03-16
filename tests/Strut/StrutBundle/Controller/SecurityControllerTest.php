<?php

namespace Tests\Strut\StrutBundle\Controller;

use Tests\Strut\StrutBundle\StrutTestCase;

class SecurityControllerTest extends StrutTestCase
{

    public function testEnabledRegistration()
    {
        $client = $this->getClient();

        $client->followRedirects();
        $crawler = $client->request('GET', '/register');
        $this->assertContains('fos_user_registration_form', $client->getResponse()->getContent());
    }
}
