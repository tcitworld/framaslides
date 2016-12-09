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
     * @route("/make-template/{id}", name="make-template", requirements={"id" = "\d+"})
     * @param Request $request
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function makeTemplateAction(Request $request, Presentation $presentation) {
        $em = $this->getDoctrine()->getManager();
        $template = $request->get('template', false) === 'true';
        $public = $request->get('public', false) === 'true';
        $presentation->setIsTemplate($template);
        $presentation->setIsPublic($public);
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
     * @route("create-from-template/{id}", name="create-from-template")
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

        if (!$request->request->has('title')) {
            $logger->warn('Title missing for forking template');
            throw new InvalidArgumentException();
        }
        $title = $request->request->get('title');

        $em = $this->getDoctrine()->getManager();

        $content = $presentation->getLastVersion()->getContent();

        $logger->info("A new version has been created for presentation " . $presentation->getTitle());
        $version = new Version();
        $version->setContent($content);
        $em->persist($version);

        $newPresentation = new Presentation($this->getUser());
        $newPresentation->setTitle($title);
        $newPresentation->addVersion($version);
        $logger->info("A new presentation has been created " . $newPresentation->getTitle());
        $em->persist($newPresentation);

        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

}
