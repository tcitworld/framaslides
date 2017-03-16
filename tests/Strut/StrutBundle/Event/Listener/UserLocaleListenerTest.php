<?php

namespace Tests\Strut\StrutBundle\Event\Listener;

use Strut\StrutBundle\Entity\Config;
use Strut\StrutBundle\Event\Listener\UserLocaleListener;
use Strut\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLocaleListenerTest extends WebTestCase
{
    public function testWithLanguage()
    {
        $session = new Session(new MockArraySessionStorage());
        $listener = new UserLocaleListener($session);

        $user = new User();
        $user->setEnabled(true);

        $config = new Config($user);
        $config->setLanguage('fr');

        $user->setConfig($config);

        $userToken = new UsernamePasswordToken($user, '', 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertEquals('fr', $session->get('_locale'));
    }

    public function testWithoutLanguage()
    {
        $session = new Session(new MockArraySessionStorage());
        $listener = new UserLocaleListener($session);

        $user = new User();
        $user->setEnabled(true);

        $config = new Config($user);

        $user->setConfig($config);

        $userToken = new UsernamePasswordToken($user, '', 'test');
        $request = Request::create('/');
        $event = new InteractiveLoginEvent($request, $userToken);

        $listener->onInteractiveLogin($event);

        $this->assertEquals('', $session->get('_locale'));
    }
}
