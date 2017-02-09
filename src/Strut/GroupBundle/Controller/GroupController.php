<?php

namespace Strut\GroupBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Strut\GroupBundle\Entity\Group;
use Strut\UserBundle\Entity\User;
use Strut\GroupBundle\Entity\UserGroup;
use Strut\GroupBundle\Form\Type\GroupPasswordValidationType;
use Strut\GroupBundle\Form\Type\NewGroupType;
use Strut\GroupBundle\Form\Type\SearchGroupType;
use Strut\GroupBundle\Service\Sha256Salted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * @Route("/", name="groups", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showPublicGroupsAction(Request $request, int $page): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var QueryBuilder $groups */
        $groups = $em->getRepository('StrutGroupBundle:Group')->findPublicGroups();
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

        return $this->render('default/groups/groups.html.twig', [
            'groups' => $pagerFanta,
            'currentPage' => $page,
        ]);
    }

	/**
	 * Search in users
	 *
	 * @Route("/search/{page}", name="group_search", defaults={"page" = "1"}, requirements={"page" = "\d+"})
	 * @Method("GET")
	 * @param $page
	 * @return Response
	 */
	public function searchAction(Request $request, int $page = 1, string $currentRoute = ''): Response
	{
		$form = $this->createForm(SearchGroupType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$groupName = $request->get('search_group')['term'];

			$em = $this->getDoctrine()->getManager();

			$users = $em->getRepository('StrutGroupBundle:Group')->findPublicGroupsByName($groupName);

			$pagerAdapter = new DoctrineORMAdapter($users);
			$pagerFanta = new Pagerfanta($pagerAdapter);
			$pagerFanta->setMaxPerPage(10);

			try {
				$pagerFanta->setCurrentPage($page);
			} catch (OutOfRangeCurrentPageException $e) {
				if ($page > 1) {
					return $this->redirect($this->generateUrl('user_index', ['page' => $pagerFanta->getNbPages()]), 302);
				}
			}

			return $this->render('default/groups/groups.html.twig', array(
				'groups' => $pagerFanta,
				'searchTerm' => $groupName,
			));
		}

		return $this->render('default/forms/search_group_form.html.twig', [
			'form' => $form->createView(),
			'currentRoute' => $currentRoute,
		]);
	}

    /**
     * @Route("/user-groups", name="my-groups", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showUserGroupsAction(Request $request, int $page): Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var QueryBuilder $groups */
        $groups = $em->getRepository('StrutGroupBundle:Group')->findGroupsByUser($this->getUser());
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

        return $this->render('default/groups/groups.html.twig', [
            'groups' => $pagerFanta,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/join/{group}", name="group_join")
     * @param Group $group
     * @return Response
     */
    public function joinGroupAction(Group $group): Response
    {
        $em = $this->getDoctrine()->getManager();

        if ($group->getAcceptSystem() === Group::ACCESS_PASSWORD) {
            return $this->redirectToRoute('group_password', ['group' => $group->getId()]);
        }
        $this->getUser()->addAGroup($group, $group->getDefaultRole());

        $em->flush();

        return $this->redirect($this->generateUrl('groups'), 302);
    }

    /**
     * @Route("/password/{group}", name="group_password")
     */
    public function checkPasswordAction(Request $request, Group $group): Response
    {
        $logger = $this->get('logger');

        $passwordForm = $this->createForm(GroupPasswordValidationType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted()) {
            $logger->info("Let's see if the password is correct !");

            $encoder = $this->get('sha256salted_encoder');
            if ($encoder->isPasswordValid($group->getPassword(), $passwordForm->getData()['password'], $this->getParameter('secret'))) {
                $em = $this->getDoctrine()->getManager();

                $this->getUser()->addAGroup($group, $group->getDefaultRole());
                $this->getUser()->getUserGroupFromGroup($group)->setAccepted(true);

                $em->flush();

                $logger->info("Password is correct !");

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    $this->get('translator')->trans('flashes.group.notice.password_success', ['%group%' => $group->getName()])
                );

                return $this->redirectToRoute('my-groups');
            }
        }
        $logger->info('Form not submitted');

        return $this->render('default/groups/password.html.twig', array(
            'form' => $passwordForm->createView(),
        ));
    }

    /**
     * @Route("/delete/{group}", name="group_delete")
     * @param Group $group
     * @return Response
     */
    public function deleteGroupAction(Group $group): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_ADMIN) {
            throw $this->createAccessDeniedException();
        }

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
     * @Route("/leave/{group}", name="group_leave")
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
     * @Route("/new", name="group_new")
     */
    public function createNewGroupAction(Request $request): Response
    {
        $group = new Group();

        $form = $this->createForm(NewGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($group->getAcceptSystem() == Group::ACCESS_PASSWORD) {
                /** @var Sha256Salted $encoder */
                $encoder = $this->get('sha256salted_encoder');
                $password = $encoder->encodePassword($group->getPassword(), $this->getParameter('secret'));
                $group->setPassword($password);
            }

            $em->persist($group);

            $groupUser = new UserGroup($this->getUser(), $group, Group::ROLE_ADMIN);
            $groupUser->setAccepted(true);
            $em->persist($groupUser);
            $em->flush();

            return $this->redirectToRoute('groups');
        }

        return $this->render('default/forms/new_group_form.html.twig', [
            'form' => $form->createview(),
        ]);
    }

    /**
     * @Route("/requests/{group}/{page}", name="group-requests", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showRequestsAction(Group $group, int $page): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            $this->createAccessDeniedException();
        }

        $requests = $group->getRequests();
        $pagerAdapter = new ArrayAdapter($requests->toArray());

        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('groups', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('default/groups/requests.html.twig', [
            'requests' => $pagerFanta,
            'group' => $group,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/activate/{group}/{user}/{accept}", name="group-activate", requirements={"accept" = "\d+"})
     * @param Group $group
     * @param User $user
     * @param $accept
     * @return Response
     */
    public function postRequestAction(Group $group, User $user, $accept): Response
    {
        if (!$this->getUser() < Group::ROLE_MANAGE_USERS) {
            $this->createAccessDeniedException("You don't have the rights to do this");
        }

        $em = $this->getDoctrine()->getManager();

        $accept = $accept == 1;
        $user->getUserGroupFromGroup($group)->setAccepted($accept);
        if (!$accept) {
            $em->remove($user->getUserGroupFromGroup($group));
        }

        $em->flush();

        return $this->redirectToRoute('groups');
    }

    /**
     * @Route("/manage/{group}/{page}", name="group-manage", defaults={"page" = "1"})
     * @param Group $group
     * @return Response
     */
    public function manageGroupUsersAction(Group $group, int $page): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            $this->createAccessDeniedException();
        }

        $members = $this->get('strut.users_repository')->findGroupMembers($group);

        $pagerAdapter = new DoctrineORMAdapter($members->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('groups', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('default/groups/manage.html.twig', [
            'members' => $pagerFanta,
            'group' => $group,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/user-edit/{group}/{user}", name="group-user-edit")
     * @param Request $request
     * @param Group $group
     * @param User $user
     * @return Response
     */
    public function editGroupUsersAction(Request $request, Group $group, User $user): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            $this->createAccessDeniedException();
        }

        $groupUser = $user->getUserGroupFromGroup($group);
        $editForm = $this->createForm('Strut\GroupBundle\Form\Type\UserGroupType', $groupUser);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupUser);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.group.notice.user.edited', ['%user%' => $user->getUsername(), '%group%' => $group->getName()])
            );

            return $this->redirectToRoute('group-manage', ['group' => $group->getId()]);
        }

        return $this->render('default/groups/edit_user.html.twig', array(
            'user' => $user,
			'group' => $group,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * @Route("/user-exclude/{group}/{user}", name="group-user-exclude")
     * @param Group $group
     * @param User $user
     * @return Response
     */
    public function excludeMemberAction(Group $group, User $user): Response
    {
        $logger = $this->get('logger');
        $logger->info('User ' . $this->getUser()->getUsername() . ' wants to exclude user ' . $user->getUsername() . ' from group ' . $group->getName());

        if (!$this->getUser()->inGroup($group) || $this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            $logger->info('User ' . $this->getUser()->getUsername() . ' has not enough rights on group ' . $group->getName() . ' to exclude user ' . $user->getUsername());
            throw $this->createAccessDeniedException();
        }

        if ($user->inGroup($group) && $user->getGroupRoleForUser($group) < Group::ROLE_ADMIN) {
            $em = $this->getDoctrine()->getManager();

            $logger->info('Removing user ' . $this->getUser()->getUsername() . ' from group ' . $group->getName());
            $em->remove($this->getUser()->getUserGroupFromGroup($group));

            $em->flush();

            return $this->redirectToRoute('group-manage', ['group' => $group->getId()]);
        }
        throw $this->createAccessDeniedException();
    }

    /**
     * @Route("/edit/{group}", name="group-edit")
     * @param Request $request
     * @param Group $group
     * @param User $user
     * @return Response
     */
    public function editGroupAction(Request $request, Group $group): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_ADMIN) {
            $this->createAccessDeniedException();
        }

        $editForm = $this->createForm(NewGroupType::class, $group);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($group->getAcceptSystem() === Group::ACCESS_PASSWORD) {
                $encoder = $this->get('sha256salted_encoder');
                $password = $encoder->encodePassword($group->getPlainPassword(), $this->getParameter('secret'));
                $group->setPassword($password);
            }

            $em->persist($group);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.group.notice.edited', ['%group%' => $group->getName()])
            );

            return $this->redirectToRoute('group-manage', ['group' => $group->getId()]);
        }

        return $this->render('default/groups/edit_group.html.twig', array(
            'group' => $group,
            'form' => $editForm->createView(),
        ));
    }
}
