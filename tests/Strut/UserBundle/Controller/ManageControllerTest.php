<?php

namespace Strut\UserBundle\Tests\Controller;

use Strut\UserBundle\Entity\User;
use Tests\Strut\StrutBundle\StrutTestCase;

class ManageControllerTest extends StrutTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/users');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testCompleteScenario()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // Create a new user in the database
        $crawler = $client->request('GET', '/users');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET /users/');
        $crawler = $client->click($crawler->filter('a.new-user')->link());

        // Fill in the form and submit it
        $form = $crawler->filter('#new_user_save')->form([
            'new_user[username]' => 'test_user',
            'new_user[email]' => 'test@test.io',
            'new_user[plainPassword][first]' => 'test',
            'new_user[plainPassword][second]' => 'test',
        ]);

        $client->submit($form);
        $client->followRedirect();
        $crawler = $client->request('GET', '/users');

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("test_user")')->count(), 'Missing element td:contains("test_user")');

        // Edit the user
		/** @var User $user */
		$user = $client->getContainer()
			->get('fos_user.user_manager')
			->findUserBy(['username' => 'test_user']);

        $crawler = $client->request('GET','/users/' . $user->getId() . '/edit');

        $form = $crawler->filter('#user_save')->form([
            'user[username]' => 'testuser',
            'user[email]' => 'test@test.io',
            'user[enabled]' => true,
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo User"
        $this->assertGreaterThan(0, $crawler->filter('[value="testuser"]')->count(), 'Missing element [value="testuser"]');

        $crawler = $client->request('GET', '/users');
        $crawler = $client->click($crawler->filter('a.edit-user')->last()->link());

        // Delete the user
        $client->submit($crawler->filter('.btn.btn-danger')->form());
        $crawler = $client->followRedirect();

        // Check the user has been delete on the list
        $this->assertNotRegExp('/^((?!testuser).)*$/s', $client->getResponse()->getContent());
    }

    public function testDeleteDisabledForLoggedUser()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/users/' . $this->getLoggedInUserId() . '/edit');
        $disabled = $crawler->filter('.btn.btn-danger')->extract('disabled');

        $this->assertEquals('disabled', $disabled[0]);
    }
}
