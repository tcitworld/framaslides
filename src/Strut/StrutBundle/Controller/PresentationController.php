<?php

namespace Strut\StrutBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Patchwork\Utf8;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Strut\GroupBundle\Entity\Group;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Form\Type\SearchEntryType;
use Strut\StrutBundle\Repository\PresentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
     * @Route("/templates-public/{page}", name="templates-public", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showPublicTemplatesAction(Request $request, int $page): Response
    {
        return $this->showPresentations('public-templates', $request, $page);
    }

    /**
     * @Route("/templates-published/{page}", name="templates-published", defaults={"page" = "1"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showPublishedTemplatesAction(Request $request, int $page): Response
    {
        return $this->showPresentations('published-templates', $request, $page);
    }

    /**
     * @Route("/presentations/group/{group}/{page}", name="group-presentations", defaults={"page" = "1"}, requirements={"page": "\d+", "group": "\d+"})
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
     * @Route("/presentations/group/list/{page}", name="group-presentations-list", defaults={"page" = "1"})
     *
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function showAnyGroupSharedTemplatesAction(Request $request, int $page): Response
    {
        $repository = $this->get('strut.presentation_repository');

        $userGroups = $this->getUser()->getGroups();
        $presentations = [];

        foreach ($userGroups as $group) {
			$presentations = array_merge($presentations, $repository->findByGroup($group)->getQuery()->getResult());
		}

        $pagerAdapter = new ArrayAdapter($presentations, true, false);
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

		$firstPicture = $this->get('strut.slides.first_picture');

        return $this->render(':default:presentations.html.twig', [
            'presentations' => $pagerFanta,
            'currentPage' => $page,
			'firstPicture' => $firstPicture,
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

        $firstPicture = $this->get('strut.slides.first_picture');

        return $this->render(':default:presentations.html.twig', [
            'presentations' => $pagerFanta,
            'currentPage' => $page,
            'searchTerm' => $searchTerm,
			'firstPicture' => $firstPicture,
        ]);
    }

    /**
     * @Route("/versions/purge/{presentation}", name="purge-version")
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function purgeVersionsAction(Presentation $presentation): Response
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);
        $em = $this->getDoctrine()->getManager();
        $versions = $presentation->getVersions();
        foreach ($versions as $version) {
            if ($version != $versions->first()) {
                $em->remove($version);
            }
        }
        $em->flush();
		return $this->redirect($this->generateUrl('versions', ['presentation' => $presentation->getId() ]), 302);
    }

    /**
     * @Route("/presentation/export/{presentation}", name="export-presentation")
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
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/presentation/delete/id/{presentation}", name="delete-presentation-id")
     * @param Request $request
     * @param Presentation $presentation
     * @return Response
     */
    public function deletePresentationAction(Request $request, Presentation $presentation): Response
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

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
     * Get public URL for entry (and generate it if necessary).
     *
     * @param Presentation $presentation
     *
     * @Route("share/{presentation}", requirements={"presentation" = "\d+"}, name="share")
     *
     * @return Response
     */
    public function shareAction(Presentation $presentation): Response
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

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
     * @Route("share/delete/{presentation}", requirements={"presentation" = "\d+"}, name="delete_share")
     *
     * @return Response
     */
    public function deleteShareAction(Presentation $presentation): Response
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

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
	 * @Cache(maxage="25200", smaxage="25200", public=true)
     *
     * @return Response
     */
    public function sharePresentationAction(Presentation $presentation): Response
    {
		/** @var \Strut\SlideBundle\Entity\Presentation $presentationEntity */
		$presentationEntity = $this->get('strut.slides.mapper')
			->setPresentation($presentation)
			->mapper();

        return $this->render('default/slides/render.html.twig', [
            'presentation' => $presentationEntity
        ]);
    }

    /**
    * @param Request $request
    * @param int     $page
    * @param string $currentRoute
    *
    * @Route("presentation/search/{page}", name="search", defaults={"page" = "1"})
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
