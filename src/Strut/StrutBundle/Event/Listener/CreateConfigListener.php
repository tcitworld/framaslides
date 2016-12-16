<?php

namespace Strut\StrutBundle\Event\Listener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Strut\StrutBundle\Entity\Config;

/**
 * This listener will create the associated configuration when a user register.
 * This configuration will be created right after the registration (no matter if it needs an email validation).
 */
class CreateConfigListener implements EventSubscriberInterface
{
    private $em;
    private $language;

    public function __construct(EntityManager $em, $language)
    {
        $this->em = $em;
        $this->language = $language;
    }

    public static function getSubscribedEvents()
    {
        return [
            // when a user register using the normal form
            FOSUserEvents::REGISTRATION_COMPLETED => 'createConfig',
            // when we manually create a user using the command line
            // OR when we create it from the config UI
            FOSUserEvents::USER_CREATED => 'createConfig',
        ];
    }

    public function createConfig(UserEvent $event, $eventName = null, EventDispatcherInterface $eventDispatcher = null)
    {
        $config = new Config($event->getUser());
        $config->setLanguage($this->language);

        $this->em->persist($config);
        $this->em->flush();
    }
}
