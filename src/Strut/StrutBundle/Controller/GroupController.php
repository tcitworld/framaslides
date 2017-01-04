<?php

namespace Strut\StrutBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Monolog\Logger;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Group;
use Strut\StrutBundle\Entity\User;
use Strut\StrutBundle\Entity\UserGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * @Route("/groups", name="groups", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showGroupsAction(Request $request, int $page): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var QueryBuilder $groups */
        $groups = $em->getRepository('Strut:Group')->getBuilder();
        $pagerAdapter = new DoctrineORMAdapter($groups->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('groups', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render(':default:groups.html.twig', [
            'groups' => $pagerFanta,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/join-group/{group}", name="group_join")
     */
    public function joinGroupAction(Group $group): Response
    {
        $em = $this->getDoctrine()->getManager();
        $this->getUser()->addAGroup($group, $group->getDefaultRole());

        $em->flush();

        return $this->redirect($this->generateUrl('groups'), 302);
    }

    /**
     * @Route("/delete-group/{group}", name="group_delete")
     * @param Group $group
     * @return Response
     */
    public function deleteGroupAction(Group $group): Response
    {
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getUserGroupFromGroup($group) && $this->getUser()->getGroupRoleForUser($group) == Group::ROLE_ADMIN) {
            /** @var User $user */
            foreach ($group->getUsers() as $user) {
                if (!$user instanceof User) {
                    continue;
                }
                $em->remove($user->getUserGroupFromGroup($group));
            }
            $em->remove($group);
        }

        $em->flush();
        return $this->redirect($this->generateUrl('groups'), 302);
    }

    /**
     * @Route("/leave-group/{group}", name="group_leave")
     * @param Group $group
     * @return Response
     */
    public function leaveGroupAction(Group $group): Response
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();
        $removeGroup = false;

        if ($this->getUser()->getGroupRoleForUser($group) == Group::ROLE_ADMIN) {
            $logger->info('User ' . $this->getUser()->getUsername() . ' is the admin for group ' . $group->getName());
            $newUser = $group->getUsers()->first();
            $newUser->setGroupRole($group, Group::ROLE_ADMIN);
            $logger->info('The new admin for group ' . $group->getName() . ' is user ' . $newUser->getUsername());
        }

        if ($group->getUsers()->count() <= 1) {
            $logger->info('User ' . $this->getUser()->getUsername() . ' was the last one on the group ' . $group->getName() . ' so it will be deleted');
            $removeGroup = true;
        }

        $logger->info('Removing user ' . $this->getUser()->getUsername() . ' from group ' . $group->getName());
        $em->remove($this->getUser()->getUserGroupFromGroup($group));

        if ($removeGroup) {
            $logger->info("Removing group " . $group->getName() . " as it doesn't contains users anymore");
            $em->remove($group);
        }

        $em->flush();
        return $this->redirect($this->generateUrl('groups'), 302);
    }

    /**
     * @Route("/new-group", name="group_new")
     */
    public function createNewGroupAction(Request $request): Response
    {
        $group = new Group();

        $form = $this->createForm('Strut\StrutBundle\Form\Type\NewGroupType', $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $group = $form->getData();
            $em->persist($group);

            $groupUser = new UserGroup($this->getUser(), $group, Group::ROLE_ADMIN);
            $em->persist($groupUser);
            $em->flush();

            return $this->redirectToRoute('groups');
        }

        return $this->render('default/forms/new_group_form.html.twig', [
            'form' => $form->createview(),
        ]);
    }

    /**
     * @Route("/group-invitations/{group}/{page}", name="group-invitations", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showRequestsAction(Group $group, int $page): Response
    {
        $userRequests = $this->get('strut.users_repository')->getRequests($group);

        $pagerAdapter = new DoctrineORMAdapter($userRequests->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('groups', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('default/requests/requests.html.twig', [
            'requests' => $pagerFanta,
            'group' => $group,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/group-activate/{group}/{user}/{accept}", name="group-activate", requirements={"accept" = "\d+"})
     *
     */
    public function postRequestAction(Group $group, User $user, $accept): Response
    {
        if (!$this->getUser() < Group::ROLE_MANAGE_USERS) {
            $this->createAccessDeniedException("You don't have the rights to do this");
        }

        $em = $this->getDoctrine()->getManager();

        $accept = $accept == 1;
        $user->getUserGroupFromGroup($group)->setAccepted($accept);
        if (!$accept)  {
            $em->remove($user->getUserGroupFromGroup($group));
        }

        $em->flush();

        return $this->redirectToRoute('groups');
    }
}
