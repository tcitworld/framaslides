<?php

namespace Strut\StrutBundle\Controller;

use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Presentation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}
