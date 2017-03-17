<?php

namespace Strut\StrutBundle\DataFixtures\ORM;

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

		$presentationAdmin = new Presentation($admin);

    	$presentationAdmin->setTitle('Presentation for admin');

		$manager->persist($presentationAdmin);

    	$version = new Version($presentationAdmin);

    	$framaslidesFile = file_get_contents(__DIR__ . '/../Content/TutoFramaslides.json');
    	$version->setContent($framaslidesFile);

		$manager->persist($version);

		$presentationAdmin->addVersion($version);

		$manager->persist($presentationAdmin);

		$presentationAdmin2 = new Presentation($admin);

		$presentationAdmin2->setTitle('Presentation 2 for admin');

		$manager->persist($presentationAdmin2);

		$version2 = new Version($presentationAdmin2);

		$framaslidesFile = file_get_contents(__DIR__ . '/../Content/TutoFramaslides.json');
		$version2->setContent($framaslidesFile);

		$manager->persist($version2);

		$presentationAdmin2->addVersion($version2);

		$manager->persist($presentationAdmin2);


		/** @var User $bob */
		$bob = $this->getReference('bob-user');

		$presentationUser = new Presentation($bob);

		$presentationUser->setTitle('Test');

		$manager->persist($presentationUser);

		$version3 = new Version($presentationUser);

		$framaslidesFile = file_get_contents(__DIR__ . '/../Content/TutoFramaslides.json');
		$version3->setContent($framaslidesFile);

		$manager->persist($version3);

		$presentationUser->addVersion($version3);

		$manager->persist($presentationUser);

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
