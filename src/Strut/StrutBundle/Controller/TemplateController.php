<?php

namespace Strut\StrutBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Strut\GroupBundle\Entity\Group;
use Strut\StrutBundle\Form\Type\ForkType;
use Strut\StrutBundle\Form\Type\TemplateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class TemplateController extends Controller
{
    /**
     * @Route("/template/{presentation}", name="template", requirements={"id" = "\d+"})
     *
     */
    public function templateFormAction(Presentation $presentation, Request $request): Response
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

        $form = $this->createForm(TemplateType::class, $presentation, ['attr' => ['user' => $this->getUser()]]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $title = $presentation->getTitle();
            $versions = $presentation->getVersions()->toArray();
            $versions = array_map(function (Version $version) use ($title) {
                $content = $version->getContent();
                $data = json_decode($content);
                $data->fileName = $title;
                $content = json_encode($data);
                $version->setContent($content);
                return $version;
            }, $versions);
            $presentation->setVersions(new ArrayCollection($versions));
            $em->persist($presentation);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'flashes.presentation.success.presentation_updated'
            );

            return $this->redirect($this->generateUrl('template', [
                'presentation' => $presentation->getId(),
            ]));
        }

        return $this->render('default/forms/template.html.twig', [
            'form' => $form->createView(),
            'presentation' => $presentation,
        ]);
    }

    private function changeFileNameInContent(string $title, Version $version): Version
    {
        $content = $version->getContent();
        $data = json_decode($content);
        $data->fileName = $title;
        $content = json_encode($data);
        $version->setContent($content);
        return $version;
    }

    /**
     * @Route("/make-template/{id}", name="make-template", requirements={"id" = "\d+"})
     * @param Request $request
     * @param Presentation $presentation
     * @return JsonResponse
     */
    public function makeTemplateAction(Request $request, Presentation $presentation): JsonResponse
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $presentation);

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
     * @Route("template/fork/{template}", name="create-from-template")
     * @param Request $request
     * @param Presentation $template
     * @return JsonResponse
     */
    public function createCopyFromTemplateAction(Request $request, Presentation $template): Response
    {
		$this->get('strut.check_rights')->checkUserPresentationAction($this->getUser(), $template);

    	$presentation = new Presentation($this->getUser());

    	$form = $this->createForm(ForkType::class, $presentation);
    	$form->handleRequest($request);

		$logger = $this->get('logger');

		if (!$template->isPublic() && !$template->isTemplate() && $template->getUser() !== $this->getUser()) {
			$logger->warn('User ' . $this->getUser()->getUsername() . ' tried to fork a presentation which is not public and not his own');
			throw new InvalidArgumentException();
		}

    	if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$content = $template->getLastVersion()->getContent();

			$logger->info("A new version has been created for presentation " . $presentation->getTitle());


			$version = new Version();
			$version->setContent($content);

			$this->changeFileNameInContent($presentation->getTitle(), $version);

			$em->persist($version);

			$logger->info('The user is ' . $this->getUser());
			$presentation->addVersion($version);
			$logger->info("A new presentation has been created " . $presentation->getTitle());
			$em->persist($presentation);

			$em->flush();

			return $this->redirectToRoute('app', ['_fragment' => $presentation->getId()]);

		}
		return $this->render('default/forms/fork.html.twig', [
			'form' => $form->createView(),
			'presentation' => $presentation,
		]);
    }
}
