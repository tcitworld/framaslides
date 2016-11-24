<?php
/**
 * Created by PhpStorm.
 * User: tcit
 * Date: 15/11/16
 * Time: 17:47
 */

namespace Strut\StrutBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PresentationController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function getPresentations(Request $request) {
        $repository = $this->get('strut.presentation_repository');
        $presentations = $repository->findByUser($this->getUser());
        return $this->render(':default:presentations.html.twig', ['presentations' => $presentations]);
    }

    /**
     * @route("/presentations-json", name="presentations-json")
     * @param Request $request
     * @return JSONResponse
     */
    public function getPresentationsJson(Request $request) {
        $repository = $this->get('strut.presentation_repository');
        $presentations = $repository->findByUser($this->getUser());
        $json = $this->get('jms_serializer')->serialize($presentations, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @route("/presentation/{title}", name="presentation")
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function getPresentationDataAction(Presentation $presentation) {
        $this->checkUserAction($presentation);
        $presentationData = $presentation->getLastVersion()->getContent();
        $json = $this->get('jms_serializer')->serialize($presentationData, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @param Presentation $presentation
     * @route("/delete-presentation/{title}", name="delete-presentation")
     * @return JsonResponse
     */
    public function deletePresentation(Presentation $presentation) {
        $this->checkUserAction($presentation);
        $em = $this->getDoctrine()->getManager();
        $em->remove($presentation);
        $em->flush();

        return new JsonResponse($presentation);
    }

    /**
     * @route("/new-presentation", name="new-presentation")
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request) {
        $data = $request->get('data');
        $name = $request->get('presentation');

        $em = $this->getDoctrine()->getManager();

        /** @var Presentation $presentation */
        $presentation = $this->get('strut.presentation_repository')->findOneBy(
            [
            'user' => $this->getUser(),
            'title' => $name,
            ]);

        if ($presentation && $data === $presentation->getLastVersion()->getContent()) {
            $json = $this->get('jms_serializer')->serialize($presentation, 'json');
            return new JsonResponse($json, 304, [], true);
        }

        $version = new Version();
        $version->setContent($data);
        $em->persist($version);


        if ($presentation) { // If the presentation already exists, just add a new version
            $presentation->addVersion($version);
        } else { // otherwise, let's create an new presentation
            $presentation = new Presentation($this->getUser());
            $presentation->setTitle($name);
            $presentation->addVersion($version);
            $em->persist($presentation);

        }

        $em->flush();

        return new JsonResponse($presentation);

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
        $presentation->setRendered($previewData);
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
                return $this->render('@Strut/preview_export/reveal.html', [
                    'presentation' => $presentation
                ]);

            default:
                return new JsonResponse(null, 406);
        }

    }

    /**
     * Check if the logged user can manage the given entry.
     *
     * @param Presentation $presentation
     */
    private function checkUserAction(Presentation $presentation)
    {
        if (null === $this->getUser() || $this->getUser()->getId() != $presentation->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this presentation.');
        }
    }
}