<?php

namespace Strut\StrutBundle\Command;

use Strut\StrutBundle\Entity\Config;
use Strut\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateConfigCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('strut:config:createforoldusers')
            ->setDescription("Create config for users who don't have any")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userRepo = $this->getContainer()->get('fos_user.user_manager');
        $users = $userRepo->findUsers();

        $output->writeln(sprintf('Checking config for %s users', count($users)));
        $fixed = 0;

        $em = $this->getDoctrine()->getManager();
        foreach ($users as $user) {
            /** @var Config $config */
            /** @var User $user */
            if (!$user->getConfig()) {
                $config = new Config($user);
                $config->setLanguage('fr');
                $user->setConfig($config);
                $em->persist($user);
                $fixed++;
            }
        }
        $em->flush();

        $output->writeln(sprintf('<info>Created config for %s users</info>', $fixed));
    }

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }
}
