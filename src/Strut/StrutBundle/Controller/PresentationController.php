<?php

namespace Strut\StrutBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Patchwork\Utf8;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Group;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Strut\StrutBundle\Form\Type\SearchEntryType;
use Strut\StrutBundle\Repository\PresentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PresentationController extends Controller
{
    /**
     * @Route("/presentations/{page}", name="presentations", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function getPresentationsAction(Request $request, int $page): Response
    {
        return $this->showPresentations('all', $request, $page);
    }

    /**
     * @Route("/templates", name="templates", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function getTemplatesAction(Request $request, int $page): Response
    {
        return $this->showPresentations('templates', $request, $page);
    }

    /**
     * @Route("/templates-public", name="templates-public", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showPublicTemplatesAction(Request $request, int $page): Response
    {
        return $this->showPresentations('public-templates', $request, $page);
    }

    /**
     * @Route("/templates-published", name="templates-published", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showPublishedTemplatesAction(Request $request, int $page): Response
    {
        return $this->showPresentations('published-templates', $request, $page);
    }

    /**
     * @Route("/group-presentations/{group}/{page}", name="group-presentations", defaults={"page" = "1"})
     *
     * @param Request $request
     * @param Group $group
     * @param int $page
     * @return Response
     */
    public function showGroupSharedTemplatesAction(Request $request, Group $group, int $page): Response
    {
        if (!$this->getUser()->inGroup($group)) {
            throw $this->createAccessDeniedException();
        }

        $repository = $this->get('strut.presentation_repository');

        /** @var QueryBuilder $presentations */
        $presentations = $repository->findByGroup($group);

        $pagerAdapter = new DoctrineORMAdapter($presentations->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('group-presentations', [
                    'page' => $pagerFanta->getNbPages(),
                    'group' => $group->getId()
                ]), 302);
            }
        }

        return $this->render(':default:presentations.html.twig', [
            'presentations' => $pagerFanta,
            'currentPage' => $page,
        ]);
    }

    /**
     * @Route("/group-presentations-list/{page}", name="group-presentations-list", defaults={"page" = "1"})
     *
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showAnyGroupSharedTemplatesAction(Request $request, int $page): Response
    {
        $repository = $this->get('strut.presentation_repository');

        /** @var QueryBuilder $presentations */
        $presentations = $repository->findAllGroupShared();

        $pagerAdapter = new DoctrineORMAdapter($presentations->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('group-presentations', [
                    'page' => $pagerFanta->getNbPages(),
                ]), 302);
            }
        }

        return $this->render(':default:presentations.html.twig', [
            'presentations' => $pagerFanta,
            'currentPage' => $page,
        ]);
    }

    /**
     * @param string $action
     * @param Request $request
     * @param int $page
     * @return RedirectResponse|Response
     */
    private function showPresentations(string $action, Request $request, int $page): Response
    {
        $repository = $this->get('strut.presentation_repository');
        /** @var PresentationRepository $repository */

        $searchTerm = (isset($request->get('search_entry')['term']) ? $request->get('search_entry')['term'] : '');
        $currentRoute = (!is_null($request->query->get('currentRoute')) ? $request->query->get('currentRoute') : '');

        switch ($action) {
            case 'all':
                $presentations = $repository->getBuilderByUser($this->getUser());
                break;

            case 'search':
                $presentations = $repository->getBuilderForSearchByUser($this->getUser(), $searchTerm, $currentRoute);
                break;

            case 'templates':
                $presentations = $repository->getBuilderForTemplatesByUser($this->getUser());
                break;

            case 'public-templates':
                $presentations = $repository->getBuilderForPublicTemplatesByUser($this->getUser());
                break;

            case 'published-templates':
                $presentations = $repository->getBuilderForPublishedTemplatesByUser($this->getUser());
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Type "%s" is not implemented.', $action));
        }

        $pagerAdapter = new DoctrineORMAdapter($presentations->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(9);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('presentations', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render(':default:presentations.html.twig', [
            'presentations' => $pagerFanta,
            'currentPage' => $page,
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * @Route("/preview/{title}/{type}/", name="preview")
     * @param Presentation $presentation
     * @param $type
     * @return Response
     */
    public function previewPresentationAction(Presentation $presentation, $type): Response
    {
        switch ($type) {
            case 'impress':
                return $this->render('@Strut/preview_export/impress.html', [
                    'presentation' => $presentation
                ]);

            case 'bespoke':
                return $this->render('@Strut/preview_export/bespoke.html', [
                    'presentation' => $presentation
                ]);

            case 'handouts':
                return $this->render('@Strut/preview_export/handouts.html', [
                    'presentation' => $presentation
                ]);

            default:
                return new JsonResponse(null, 406);
        }
    }


    /**
     * @Route("purge-versions/{presentation}", name="purge-version")
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function purgeVersionsAction(Presentation $presentation): JsonResponse
    {
        $this->checkUserPresentationAction($presentation);
        $em = $this->getDoctrine()->getManager();
        $versions = $presentation->getVersions();
        foreach ($versions as $version) {
            if ($version != $versions->first()) {
                $em->remove($version);
            }
        }
        $em->flush();
        $json = $this->get('jms_serializer')->serialize($presentation, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/export-presentation/{presentation}", name="export-presentation")
     * @param Presentation $presentation
     * @return Response
     */
    public function exportPresentationAction(Presentation $presentation): Response
    {
        $response = new Response($presentation->getLastVersion()->getContent());

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Utf8::toAscii($presentation->getTitle()) . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/delete-presentation-id/{presentation}", name="delete-presentation-id")
     * @param Request $request
     * @param Presentation $presentation
     * @return Response
     */
    public function deletePresentationAction(Request $request, Presentation $presentation): Response
    {
        $this->checkUserPresentationAction($presentation);

        // entry deleted, dispatch event about it!
        // $this->get('event_dispatcher')->dispatch(EntryDeletedEvent::NAME, new EntryDeletedEvent($entry));

        $em = $this->getDoctrine()->getManager();
        $em->remove($presentation);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.presentation.notice.presentation_deleted'
        );

        return $this->redirectToRoute('presentations');
    }

    /**
     * Check if the logged user can manage the given entry.
     *
     * @param Presentation $presentation
     */
    private function checkUserPresentationAction(Presentation $presentation)
    {
        if (null === $this->getUser()) {
            $this->get('logger')->info('user is null');
            throw $this->createAccessDeniedException("Can't find user for this presentation");
        }

        if ($this->getUser()->getId() != $presentation->getUser()->getId() && $presentation->getGroupShares()->isEmpty()) {
            $this->get('logger')->info('user ' . $this->getUser()->getUsername() . ' has no rights on presentation ' . $presentation->getTitle() . ' which belongs to ' . $presentation->getUser()->getUsername());
            throw $this->createAccessDeniedException("You don't have the rights to access this presentation.");
        }

        if (!$presentation->getGroupShares()->isEmpty() && empty(array_intersect($this->getUser()->getGroups()->toArray(), $presentation->getGroupShares()->toArray()))) {
            $this->get('logger')->info('user ' . $this->getUser()->getUsername() . ' is not in one of the groups for presentation ' . $presentation->getTitle());
            throw $this->createAccessDeniedException('You are not in the group to access this presentation');
        }
    }


    /**
     * Get public URL for entry (and generate it if necessary).
     *
     * @param Presentation $presentation
     *
     * @Route("/share/{id}", requirements={"id" = "\d+"}, name="share")
     *
     * @return Response
     */
    public function shareAction(Presentation $presentation): Response
    {
        $this->checkUserPresentationAction($presentation);

        if (null === $presentation->getUuid()) {
            $presentation->generateUuid();

            $em = $this->getDoctrine()->getManager();
            $em->persist($presentation);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('share_presentation', [
            'uuid' => $presentation->getUuid(),
        ]));
    }

    /**
     * Disable public sharing for an entry.
     *
     * @param Presentation $presentation
     *
     * @Route("/share/delete/{id}", requirements={"id" = "\d+"}, name="delete_share")
     *
     * @return Response
     */
    public function deleteShareAction(Presentation $presentation): Response
    {
        $this->checkUserPresentationAction($presentation);

        $presentation->cleanUuid();

        $em = $this->getDoctrine()->getManager();
        $em->persist($presentation);
        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * Ability to view a content publicly.
     *
     * @param Presentation $presentation
     *
     * @Route("/share/{uuid}", requirements={"uuid" = ".+"}, name="share_presentation")
     *
     * @return Response
     */
    public function sharePresentationAction(Presentation $presentation): Response
    {
        return $this->render('@Strut/preview_export/impress.html', [
            'presentation' => $presentation
        ]);
    }

    /**
    * @param Request $request
    * @param int     $page
    * @param string $currentRoute
    *
    * @Route("/search/{page}", name="search", defaults={"page" = "1"})
    *
    * @return Response
    */
    public function searchFormAction(Request $request, int $page = 1, string $currentRoute = ''): Response
    {
        $form = $this->createForm(SearchEntryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->showPresentations('search', $request, $page);
        }

        return $this->render('default/forms/search_form.html.twig', [
            'form' => $form->createView(),
            'currentRoute' => $currentRoute,
        ]);
    }
}