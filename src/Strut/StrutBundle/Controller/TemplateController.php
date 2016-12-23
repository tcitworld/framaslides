<?php

namespace Strut\StrutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class TemplateController extends Controller {



    /**
     * @Route("/make-template/{id}", name="make-template", requirements={"id" = "\d+"})
     * @param Request $request
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function makeTemplateAction(Request $request, Presentation $presentation) {
        $this->checkUserPresentationAction($presentation);

        $em = $this->getDoctrine()->getManager();
        $template = $request->get('template', false) === 'true';
        $public = $request->get('public', false) === 'true';
        $presentation->setTemplate($template);
        $presentation->setPublic($public);
        $em->flush();

        $json = $this->get('jms_serializer')->serialize($presentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("create-from-template/{id}", name="create-from-template")
     * @param Request $request
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function createCopyFromTemplateAction(Request $request, Presentation $presentation) {
        $logger = $this->get('logger');

        if (!$presentation->isTemplate() && !$request->request->has('export')) {
            $logger->warn('User ' . $this->getUser()->getName() . ' tried to fork a presentation which is not a template');
            throw new InvalidArgumentException();
        }
        if (!$presentation->isPublic() && $presentation->getUser() !== $this->getUser()) {
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

        /**
         * Modify content for fileName
         */
        $data = json_decode($content);
        $data->fileName = $title;
        $content = json_encode($data);

        $version = new Version();
        $version->setContent($content);
        $em->persist($version);

        $logger->info('The user is '. $this->getUser());
        $newPresentation = new Presentation($this->getUser());
        $newPresentation->setTitle($title);
        $newPresentation->addVersion($version);
        $logger->info("A new presentation has been created " . $newPresentation->getTitle());
        $em->persist($newPresentation);

        $em->flush();

        $json = $this->get('jms_serializer')->serialize($newPresentation, 'json');

        return (new JsonResponse())->setJson($json);
    }

    /**
     * @param Presentation $presentation
     */
    private function checkUserPresentationAction(Presentation $presentation)
    {
        if (null === $this->getUser() || $this->getUser()->getId() != $presentation->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this presentation.');
        }
    }

}
