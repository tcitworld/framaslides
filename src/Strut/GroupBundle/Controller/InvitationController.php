<?php

namespace Strut\GroupBundle\Controller;

use Strut\GroupBundle\Entity\Group;
use Strut\GroupBundle\Entity\Invitation;
use Strut\UserBundle\Entity\User;
use Strut\GroupBundle\Entity\UserGroup;
use Strut\GroupBundle\Form\Type\SendInvitationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationController extends Controller
{
    /**
     * @Route("/invitations/{group}", name="group-invitations")
     * @param Group $group
     * @return Response
     */
    public function showInvitationsAction(Group $group): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            throw $this->createAccessDeniedException();
        }

        $invitations = $group->getInvited();
        return $this->render('default/groups/invitations/invitations.html.twig', [
            'invitedUsers' => $invitations,
            'group' => $group,
        ]);
    }

    /**
     * @Route("/invitation/{group}", name="group-invitation")
     */
    public function createNewGroupAction(Request $request, Group $group): Response
    {
        if ($this->getUser()->getGroupRoleForUser($group) < Group::ROLE_MANAGE_USERS) {
            throw $this->createAccessDeniedException();
        }


        $form = $this->createForm(SendInvitationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $username = $form->getData()->getUsername();
            $userRepo = $this->get('strut.users_repository');
            /** @var User $user */
            $user = $userRepo->findOneByUsername($username);

            if (!$user) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'flashes.group.error.no_such_user'
                );
                return $this->render('default/groups/invitations/new_invitation.html.twig', [
                    'form' => $form->createview(),
                ]);
            }


            /**
             * If the user already has received an invite or is in the group
             */
            $userGroupRepo = $this->get('strut.user_group_repository');

            if ($userGroupRepo->findOneBy(
                [
                    'user' => $user->getId(),
                    'group' => $group->getId(),
                ]
            )) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'flashes.group.error.already_in'
                );
                return $this->render('default/groups/invitations/new_invitation.html.twig', [
                    'form' => $form->createview(),
                ]);
            }

            $userGroup = new UserGroup($user, $group, $group->getDefaultRole());

            $invite = new Invitation($userGroup);

            $userGroup->setInvitation($invite);

            $em->persist($userGroup);

            $em->flush();

            $urlCode = $this->get('router')->generate('invitation-valid', ['code' => $invite->getCode()], UrlGeneratorInterface::ABSOLUTE_URL);

            $message = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('group.invitation.email.subject', ['%group%' => $userGroup->getGroup()->getName()]))
				->setFrom($this->getParameter('fos_user.from_email.address'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'default/emails/invitation.html.twig',
                        [
                            'url' => $urlCode,
                            'username' => $userGroup->getUser()->getUsername(),
                            'groupname' => $group->getName(),
                        ]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'default/emails/invitation.txt.twig',
                        [
                            'url' => $urlCode,
                            'username' => $userGroup->getUser()->getUsername(),
                            'groupname' => $group->getName(),
                        ]
                    ),
                    'text/plain'
                )
            ;
            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add(
                'success',
                'flashes.group.success.invite_sent'
            );

            return $this->redirectToRoute('group-invitations', ['group' => $group->getId()]);
        }

        return $this->render('default/groups/invitations/new_invitation.html.twig', [
            'form' => $form->createview(),
        ]);
    }

    /**
     * @Route("/invitation-valid/{code}", name="invitation-valid")
     */
    public function validInvitationAction(Invitation $invitation): Response
    {
        if ($this->getUser() != $invitation->getUserGroup()->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $userGroup = $invitation->getUserGroup();

        $userGroup->setInvitation(null);
        $em->remove($invitation);

        $userGroup->setAccepted(true);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            $this->get('translator')->trans('flashes.group.notice.invite_accepted', ['%group%' => $userGroup->getGroup()->getName()])
        );

        return $this->redirectToRoute('group-presentations', ['group' => $invitation->getUserGroup()->getGroup()->getId()]);
    }

    /**
     * @Route("invitation-cancel/{code}", name="invitation-cancel")
     */
    public function cancelInvitationAction(Invitation $invitation): Response
    {
        if ($this->getUser()->getGroupRoleForUser($invitation->getUserGroup()->getGroup()) < Group::ROLE_MANAGE_USERS) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($invitation);
        $em->remove($invitation->getUserGroup());
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.group.notice.invite_cancelled'
        );

        return $this->redirectToRoute('group-invitations', ['group' => $invitation->getUserGroup()->getGroup()->getId()]);
    }
}
