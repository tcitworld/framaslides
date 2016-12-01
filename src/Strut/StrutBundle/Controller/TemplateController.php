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
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

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

    /**
     * @route("create-from-template/{presentation}", name="create-from-template")
     * @param Request $request
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function createCopyFromTemplateAction(Request $request, Presentation $presentation) {
        $logger = $this->get('logger');

        if (!$presentation->getIsTemplate()) {
            $logger->warn('User ' . $this->getUser()->getName() . ' tried to fork a presentation which is not a template');
            throw new InvalidArgumentException();
        }
        if (!$presentation->getIsPublic() && $presentation->getUser() !== $this->getUser()) {
            $logger->warn('User ' . $this->getUser()->getName() . ' tried to fork a presentation which is not public and not his own');
            throw new InvalidArgumentException();
        }

        $em = $this->getDoctrine()->getManager();

        $content = $presentation->getLastVersion()->getContent();
        $title = $request->get('title');

        $logger->info("A new version has been created for presentation " . $presentation->getTitle());
        $version = new Version();
        $version->setContent($content);
        $em->persist($version);

        $newPresentation = new Presentation($this->getUser());
        $newPresentation->setTitle($title);
        $newPresentation->addVersion($version);
        $logger->info("A new presentation has been created " . $presentation->getTitle());
        $em->persist($presentation);

        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

}
