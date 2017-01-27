<?php

namespace Strut\UserBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Strut\UserBundle\Form\Type\SearchUserType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

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
    public function indexAction(int $page): Response
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('StrutUserBundle:User')->findAll();

        $pagerAdapter = new ArrayAdapter($users);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(10);

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
     * Search in users
     *
     * @Route("search/{page}", name="user_search", defaults={"page" = "1"}, requirements={"page" = "\d+"})
     * @Method("GET")
     * @param $page
     * @return Response
     */
    public function searchAction(Request $request, int $page = 1, string $currentRoute = ''): Response
    {
        $form = $this->createForm(SearchUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        	$username = $request->get('search_user')['term'];

            $em = $this->getDoctrine()->getManager();

            $users = $em->getRepository('StrutUserBundle:User')->searchUsers($username);

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

            return $this->render('default/manage.html.twig', array(
                'users' => $pagerFanta,
				'searchTerm' => $username,
            ));
        }

        return $this->render('default/forms/search_user_form.html.twig', [
            'form' => $form->createView(),
            'currentRoute' => $currentRoute,
        ]);
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newAction(Request $request): Response
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        // enable created user by default

        $form = $this->createForm('Strut\UserBundle\Form\Type\NewUserType', $user, [
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
    public function editAction(Request $request, User $user): Response
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('Strut\UserBundle\Form\Type\UserType', $user);
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
    public function deleteAction(Request $request, User $user): RedirectResponse
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
     * @return Form The form
     */
    private function createDeleteForm(User $user): Form
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
