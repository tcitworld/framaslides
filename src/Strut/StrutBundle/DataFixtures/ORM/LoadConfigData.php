<?php

namespace Strut\StrutBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strut\StrutBundle\Entity\Config;
use Strut\UserBundle\Entity\User;

class LoadConfigData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
    	/** @var User $admin */
    	$admin = $this->getReference('admin-user');

    	/** @var Config $adminConfig */
        $adminConfig = new Config($admin);

        $adminConfig->setLanguage('en');
        $adminConfig->setListMode(0);

        $manager->persist($adminConfig);

        $admin->setConfig($adminConfig);

        $manager->persist($admin);

        $this->addReference('admin-config', $adminConfig);

        /** @var User $bob */
        $bob = $this->getReference('bob-user');

        $bobConfig = new Config($bob);
        $bobConfig->setLanguage('fr');
        $bobConfig->setListMode(1);

        $manager->persist($bobConfig);

		$bob->setConfig($bobConfig);

		$manager->persist($bob);

        $this->addReference('bob-config', $bobConfig);

        /** @var User $empty */
        $empty = $this->getReference('empty-user');

        $emptyConfig = new Config($empty);
        $emptyConfig->setLanguage('en');
        $emptyConfig->setListMode(0);

        $manager->persist($emptyConfig);

		$empty->setConfig($bobConfig);

		$manager->persist($empty);

        $this->addReference('empty-config', $emptyConfig);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
