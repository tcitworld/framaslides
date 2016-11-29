<?php

namespace Strut\StrutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller {

    /**
     * @route("/templates", name="templates")
     *
     */
    public function showTemplatesAction() {
        $repository = $this->get('strut.presentation_repository');
        $templates = $repository->getTemplates($this->getUser());
        return $this->render('default/templates.html.twig', ['templates' => $templates]);
    }

    /**
     * @route("/create-template/{id}", name="create-template", requirements={"id" = "\d+"})
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function createTemplate(Presentation $presentation = null) {
        $em = $this->getDoctrine()->getManager();
        $presentation->setIsTemplate(true);
        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * @route("/create-public/{presentation}", name="create-public")
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function createPublic(Presentation $presentation) {
        $em = $this->getDoctrine()->getManager();
        $presentation->setIsPublic(true);
        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * @route("/templates-public", name="templates-public")
     */
    public function showPublicTemplatesAction() {
        $repository = $this->get('strut.presentation_repository');
        $templates = $repository->getPublicTemplates($this->getUser());
        return $this->render('default/templates.html.twig', ['templates' => $templates]);
    }

    /**
     * @route("/templates-published", name="templates-published")
     */
    public function showPublishedTemplatesAction() {
        $repository = $this->get('strut.presentation_repository');
        $templates = $repository->getPublishedTemplates($this->getUser());
        return $this->render('default/templates.html.twig', ['templates' => $templates]);
    }

}