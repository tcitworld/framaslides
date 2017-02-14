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
			$presentations = array_merge($presentations, $repository->findByGroup($group)->getQuery()->getResult());
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
		$this->checkUserPresentationAction($presentation);

        $presentationData = $presentation->getLastVersion()->getContent();
        $json = $this->get('jms_serializer')->serialize($presentationData, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/delete-presentation/{presentation}", name="delete-presentation")
     * @param $presentationTitle
     * @return JsonResponse
     */
    public function deletePresentationAction(Presentation $presentation): JsonResponse
    {

        $em = $this->getDoctrine()->getManager();
        $em->remove($presentation);
        $em->flush();

        return new JsonResponse();
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

    	$this->checkUserPresentationAction($presentation);
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
		$this->checkUserPresentationAction($presentation);

        $previewData = $request->get('previewData');
        $previewConfig = $request->get('previewConfig');

        $presentation->setRendered($previewData);
        $presentation->setPreviewConfig($previewConfig);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new JSONResponse();
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
}
