<?php

namespace Wallabag\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Strut\UserBundle\Entity\User;

class LoadPresentationData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
		/** @var User $admin */
		$admin = $this->getReference('admin-user');

    	$presentation = new Presentation($admin);

    	$presentation->setTitle('Test');

		$manager->persist($presentation);

    	$version = new Version($presentation);

    	$framaslidesFile = file_get_contents(__DIR__ . '/../Content/TutoFramaslides.json');
    	$version->setContent($framaslidesFile);

		$manager->persist($version);

		$presentation->addVersion($version);

		$manager->persist($presentation);

		$manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 30;
    }
}
