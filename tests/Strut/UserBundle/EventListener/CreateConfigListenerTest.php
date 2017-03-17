<?php

namespace Tests\Wallabag\UserBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Strut\StrutBundle\Entity\Config;
use Strut\StrutBundle\Event\Listener\CreateConfigListener;
use Strut\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Strut\StrutBundle\StrutTestCase;

class CreateConfigListenerTest extends StrutTestCase
{
    private $em;
    private $listener;
    private $dispatcher;
    private $request;
    private $response;

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CreateConfigListener(
            $this->em,
            'fr'
        );

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->listener);

        $this->request = Request::create('/');
        $this->response = Response::create();
    }

    public function testWithValidUser()
    {
        $user = new User();
        $user->setEnabled(true);

        $event = new FilterUserResponseEvent(
            $user,
            $this->request,
            $this->response
        );

        $config = new Config($user);
        $config->setLanguage('fr');

        $this->em->expects($this->once())
            ->method('persist')
            ->will($this->returnValue($config));
        $this->em->expects($this->once())
            ->method('flush');

        $this->dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_COMPLETED,
            $event
        );
    }
}
