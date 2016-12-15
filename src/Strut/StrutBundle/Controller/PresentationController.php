<?php
/**
 * Created by PhpStorm.
 * User: tcit
 * Date: 15/11/16
 * Time: 17:47
 */

namespace Strut\StrutBundle\Controller;


use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Patchwork\Utf8;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PresentationController extends Controller
{
    /**
     * @Route("/presentations/{page}", name="presentations", defaults={"page" = "1"})
     * @param int $page
     * @return Response
     */
    public function getPresentationsAction(int $page) {
        $repository = $this->get('strut.presentation_repository');
        $presentations = $repository->getBuilderByUser($this->getUser());

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

        return $this->render(':default:presentations.html.twig', ['presentations' => $pagerFanta, 'currentPage' => $page]);
    }

    /**
     * @route("/versions/{presentation}/{page}", name="versions", defaults={"page" = "1"})
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function getPresentationVersions(Presentation $presentation, int $page) {
        $versions = $presentation->getVersions();

        $pagerAdapter = new DoctrineCollectionAdapter($versions);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(5);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('presentations', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        $versionsArray = [];
        foreach ($pagerFanta->getCurrentPageResults() as $result) {
            $versionsArray[] = $result;
        }

        $json = $this->get('jms_serializer')->serialize([
            'total' => $pagerFanta->getNbResults(),
            'versions' => $versionsArray,
            'nbPages' => $pagerFanta->getNbPages(),
            'haveToPaginate' => $pagerFanta->haveToPaginate(),
            'hasPreviousPage' => $pagerFanta->hasPreviousPage(),
            'previousPage' => !$pagerFanta->hasPreviousPage() ?? $pagerFanta->getPreviousPage(),
            'hasNextPage' => $pagerFanta->hasNextPage(),
            'nextPage' => !$pagerFanta->hasPreviousPage() ?? $pagerFanta->getNextPage(),
        ], 'json');
        return (new JsonResponse())->setJson($json);
    }

    /** API Stuff */

    /**
     * @route("/presentations-json", name="presentations-json")
     * @return JSONResponse
     */
    public function getPresentationsJson() {
        $repository = $this->get('strut.presentation_repository');
        $presentations = $repository->findByUser($this->getUser());
        $json = $this->get('jms_serializer')->serialize($presentations, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @route("/presentation/{presentationTitle}", name="presentation")
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function getPresentationDataAction($presentationTitle) {
        $presentation = $this->get('strut.presentation_repository')->findOneBy([
            'title' => $presentationTitle,
            'user' => $this->getUser(),
        ]);
        if (!$presentation) {
            return new JsonResponse([], 404);
        }
        $presentationData = $presentation->getLastVersion()->getContent();
        $json = $this->get('jms_serializer')->serialize($presentationData, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @route("/delete-presentation/{presentationTitle}", name="delete-presentation")
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function deletePresentation($presentationTitle) {
        $presentation = $this->get('strut.presentation_repository')->findOneBy([
            'title' => $presentationTitle,
            'user' => $this->getUser(),
        ]);
        if (!$presentation) {
            return new JsonResponse([], 404);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($presentation);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * @route("/delete-version/{version}", name="delete-version")
     * @param Version $version
     * @return JsonResponse
     */
    public function deleteVersion(Version $version) {
        $this->checkUserVersionAction($version);
        $em = $this->getDoctrine()->getManager();
        $version->getPresentation()->removeVersion($version);
        $em->remove($version);
        $em->flush();

        return new JsonResponse($version);
    }

    /**
     * @route("/restore-version/{version}", name="restore-version")
     * @param Version $version
     * @return JsonResponse
     */
    public function restoreVersion(Version $version) {
        $this->checkUserVersionAction($version);
        $em = $this->getDoctrine()->getManager();

        $presentation = $version->getPresentation();

        $newVersion = new Version($presentation);
        $newVersion->setContent($version->getContent());

        $presentation->addVersion($newVersion);

        $em->persist($newVersion);
        $em->flush();

        $versions = $presentation->getVersions();
        $json = $this->get('jms_serializer')->serialize($versions, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * @route("/new-presentation", name="new-presentation")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request) {
        $data = $request->get('data');
        $newEntry = (bool) $request->get('newEntry', 0);
        $name = $request->get('presentation');

        $em = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');

        /** @var Presentation $presentation */
        $presentation = $this->get('strut.presentation_repository')->findOneBy(
            [
            'user' => $this->getUser(),
            'title' => $name,
            ]);

        if ($presentation && $data === $presentation->getLastVersion()->getContent()) {
            $logger->info("Tried to save but there's no change for presentation " . $presentation->getId());
            $json = $this->get('jms_serializer')->serialize($presentation, 'json');
            return new JsonResponse($json, 304, [], true);
        }

        $version = new Version();
        $version->setContent($data);
        $em->persist($version);
        $logger->info("Created version " . $version->getId());


        if ($presentation) { // If the presentation already exists, just add a new version
            $logger->info("Version  " . $version->getId() . " has been added to presentation " . $presentation->getId());
            $presentation->addVersion($version);
        } else if ($newEntry) { // otherwise, let's create an new presentation
            $presentation = new Presentation($this->getUser());
            $presentation->setTitle($name);
            $presentation->addVersion($version);
            $logger->info("A new presentation has been created " . $presentation->getTitle());
            $em->persist($presentation);
        } else {
            return new JsonResponse([]);
        }

        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);

    }

    /**
     * @route("/save-preview/{title}", name="save-preview")
     * @param Request $request
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function savePreview(Request $request, Presentation $presentation) {
        //$this->checkUserAction($presentation);
        $previewData = $request->get('previewData');
        $previewConfig = $request->get('previewConfig');

        $presentation->setRendered($previewData);
        $presentation->setPreviewConfig($previewConfig);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new JSONResponse();

    }

    /**
     * @route("/preview/{title}/{type}/", name="preview")
     * @param Presentation $presentation
     * @param $type
     * @return Response
     */
    public function previewPresentationAction(Presentation $presentation, $type) {
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
     * @route("/export-presentation/{presentation}", name="export-presentation")
     * @param Presentation $presentation
     * @return Response
     */
    public function exportPresentation(Presentation $presentation) {
        $response = new Response($presentation->getLastVersion()->getContent());

        // $response->headers->set('Content-Type', 'text/plain');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Utf8::toAscii($presentation->getTitle()) . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @route("/export-version/{version}", name="export-version")
     * @param Version $version
     * @return Response
     */
    public function exportVersion(Version $version) {
        $response = new Response($version->getContent());

        // $response->headers->set('Content-Type', 'text/plain');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Utf8::toAscii($version->getPresentation()->getTitle()) . '-' . $version->getUpdatedAt()->format('d-m-Y H:i:s') . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
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
            throw $this->createAccessDeniedException('You can not access this presentation.');
        }

        if ($this->getUser()->getId() != $presentation->getUser()->getId()) {
            var_dump($this->getUser()->getId());
            var_dump($presentation->getUser()->getId());
            $this->get('logger')->info('user ' . $this->getUser()->getName() . ' has no rights on presentation ' . $presentation->getTitle() . ' which belongs to ' . $presentation->getUser()->getName());
            throw $this->createAccessDeniedException('You can not access this presentation.');
        }
    }

    /**
     * Check if the logged user can manage the given entry.
     *
     * @param Version $version
     */
    private function checkUserVersionAction(Version $version)
    {
        if (null === $this->getUser() || $this->getUser()->getId() != $version->getPresentation()->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this presentation.');
        }
    }

    /**
     * Get public URL for entry (and generate it if necessary).
     *
     * @param Presentation $presentation
     *
     * @Route("/share/{id}", requirements={"id" = "\d+"}, name="share")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shareAction(Presentation $presentation)
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteShareAction(Presentation $presentation)
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
     * @Cache(maxage="25200", smaxage="25200", public=true)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sharePresentationAction(Presentation $presentation)
    {
        return $this->render('@Strut/preview_export/impress.html', [
            'presentation' => $presentation
        ]);
    }
}
