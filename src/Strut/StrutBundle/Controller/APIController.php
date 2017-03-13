<?php

namespace Strut\StrutBundle\Controller;

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

		/** @var Presentation[] $presentations */
		/** User presentations */
        $presentations = $repository->findByUser($this->getUser());

		$userGroups = $this->getUser()->getGroups();
		foreach ($userGroups as $group) {
			$presentations = array_unique(array_merge($presentations, $repository->findByGroup($group)->getQuery()->getResult()), SORT_REGULAR);
		}

        $json = $this->get('jms_serializer')->serialize($presentations, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/presentation/{presentation}", name="presentation")
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function getPresentationDataAction(Presentation $presentation): JsonResponse
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

        $presentationData = $presentation->getLastVersion()->getContent();
        $json = $this->get('jms_serializer')->serialize($presentationData, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/delete-presentation/{presentation}", name="delete-presentation", requirements={"presentation": "\d+"})
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function deletePresentationAction(Presentation $presentation): JsonResponse
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($presentation);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

	/**
	 * @Route("/create-presentation/", name="create-presentation")
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function createPresentationAction(Request $request): JsonResponse
	{
		$data = $request->get('data');
		$title = $request->get('title');

		$em = $this->getDoctrine()->getManager();
		$logger = $this->get('logger');

		$version = new Version();
		$version->setContent($data);
		$em->persist($version);
		$logger->info("Created version " . $version->getId());

		$presentation = new Presentation($this->getUser());
		$presentation->setTitle($title);
		$presentation->addVersion($version);
		$logger->info("A new presentation has been created " . $presentation->getTitle());
		$em->persist($presentation);

		$em->flush();

		$json = $this->get('jms_serializer')->serialize($presentation, 'json');

		return (new JsonResponse())->setJson($json);
	}

    /**
     * @Route("/new-presentation/{presentation}", name="new-presentation", requirements={"presentation": "\d+"})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request, Presentation $presentation): JsonResponse
    {

		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);
        $data = $request->get('data');

        $em = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');

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
        } else {
            return new JsonResponse([]);
        }

        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/save-preview/{presentation}", name="save-preview", requirements={"presentation": "\d+"})
     * @param Request $request
     * @param string $title
     * @return JsonResponse
     */
    public function savePreviewAction(Request $request, Presentation $presentation): JsonResponse
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

        $previewData = $request->get('previewData');
        $previewConfig = $request->get('previewConfig');

        $presentation->setRendered($previewData);
        $presentation->setPreviewConfig($previewConfig);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new JSONResponse();
    }
}
