<?php

namespace Strut\StrutBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

//use Strut\StrutBundle\Entity\Config;

/**
 * User controller.
 */
class ManageController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/{page}", name="user_index", defaults={"page" = "1"}, requirements={"page" = "\d+"})
     * @Method("GET")
     * @param $page
     * @return Response
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('Strut:User')->findAll();

        $pagerAdapter = new ArrayAdapter($users);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(2);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('user_index', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('default/manage.html.twig', array(
            'users' => $pagerFanta,
        ));
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        // enable created user by default

        $form = $this->createForm('Strut\StrutBundle\Form\NewUserType', $user, [
            'validation_groups' => ['Profile'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            // dispatch a created event so the associated config will be created
            $event = new UserEvent($user, $request);
            $this->get('event_dispatcher')->dispatch(FOSUserEvents::USER_CREATED, $event);

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.user.notice.added', ['%username%' => $user->getUsername()])
            );

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('default/Manage/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param User $user
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('Strut\StrutBundle\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.user.notice.updated', ['%username%' => $user->getUsername()])
            );

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('default/Manage/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                $this->get('translator')->trans('flashes.user.notice.deleted', ['%username%' => $user->getUsername()])
            );

            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a User entity.
     *
     * @param User $user The User entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
