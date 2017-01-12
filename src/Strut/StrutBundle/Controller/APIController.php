<?php

namespace Strut\StrutBundle\Controller;

use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class APIController extends Controller
{
    /**
     * @Route("/presentations-json", name="presentations-json")
     * @return JSONResponse
     */
    public function getPresentationsJsonAction(): JsonResponse
    {
        $repository = $this->get('strut.presentation_repository');
        $presentations = $repository->findByUser($this->getUser());
        $json = $this->get('jms_serializer')->serialize($presentations, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/presentation/{presentationTitle}", name="presentation")
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function getPresentationDataAction($presentationTitle): JsonResponse
    {
        $presentation = $this->get('strut.presentation_repository')->findOneBy([
            'title' => $presentationTitle,
            'user' => $this->getUser(),
        ]);
        /** @var Presentation $presentation */
        if (!$presentation) {
            return new JsonResponse([], 404);
        }
        $presentationData = $presentation->getLastVersion()->getContent();
        $json = $this->get('jms_serializer')->serialize($presentationData, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/delete-presentation/{presentationTitle}", name="delete-presentation")
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function deletePresentationAction($presentationTitle): JsonResponse
    {
        $presentation = $this->get('strut.presentation_repository')->findOneBy([
            'title' => $presentationTitle,
            'user' => $this->getUser(),
        ]);
        /** @var Presentation $presentation */
        if (!$presentation) {
            return new JsonResponse([], 404);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($presentation);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/new-presentation", name="new-presentation")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request): JsonResponse
    {
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
        } elseif ($newEntry) { // otherwise, let's create an new presentation
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
     * @Route("/save-preview/{title}", name="save-preview")
     * @param Request $request
     * @param string $title
     * @return JsonResponse
     */
    public function savePreviewAction(Request $request, string $title): JsonResponse
    {
        /** @var Presentation $presentation */
        $presentation = $this->get('strut.presentation_repository')->findOneBy(
            [
                'user' => $this->getUser(),
                'title' => $title,
            ]);
        if (!$presentation) {
            return new JsonResponse([], 404);
        }

        $previewData = $request->get('previewData');
        $previewConfig = $request->get('previewConfig');

        $presentation->setRendered($previewData);
        $presentation->setPreviewConfig($previewConfig);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new JSONResponse();
    }
}
