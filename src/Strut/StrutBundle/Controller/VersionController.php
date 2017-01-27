<?php

namespace Strut\StrutBundle\Controller;

use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Patchwork\Utf8;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class VersionController extends Controller
{
    /**
     * @Route("/presentation/{presentation}/versions/{page}", name="versions", defaults={"page" = "1"})
     * @param Presentation $presentation
     * @param int $page
     * @return Response
     */
    public function getVersionsAction(Presentation $presentation, int $page): Response
    {
        $versions = $presentation->getVersions();

        $pagerAdapter = new DoctrineCollectionAdapter($versions);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(10);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('presentations', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }

        return $this->render('default/versions.html.twig', [
            'versions' => $pagerFanta,
            'presentation' => $presentation,
        ]);
    }

    /**
     * @Route("/version/delete/{version}", name="delete-version-web")
     * @param Version $version
     * @return RedirectResponse
     */
    public function deleteVersionAction(Version $version): RedirectResponse
    {
        $this->checkUserVersionAction($version);
        $em = $this->getDoctrine()->getManager();
        $presentation = $version->getPresentation();
        $presentation->removeVersion($version);
        $em->remove($version);
        $em->flush();
        return $this->redirect($this->generateUrl('versions', ['presentation' => $presentation->getId()]), 302);
    }

    /**
     * @Route("/version/restore/{version}", name="restore-version")
     * @param Version $version
     * @return RedirectResponse
     */
    public function restoreVersionAction(Version $version): RedirectResponse
    {
        $this->checkUserVersionAction($version);
        $em = $this->getDoctrine()->getManager();

        $presentation = $version->getPresentation();

        $newVersion = new Version($presentation);
        $newVersion->setContent($version->getContent());

        $presentation->addVersion($newVersion);

        $em->persist($newVersion);
        $em->flush();

        return $this->redirect($this->generateUrl('versions', ['presentation' => $presentation->getId()]), 302);
    }

    /**
     * @Route("/version/export/{version}", name="export-version")
     * @param Version $version
     * @return Response
     */
    public function exportVersionAction(Version $version): Response
    {
        $response = new Response($version->getContent());

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Utf8::toAscii($version->getPresentation()->getTitle()) . '-' . $version->getUpdatedAt()->format('d-m-Y H:i:s') . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }


    /**
     * Check if the logged user can manage the given entry.
     *
     * @param Version $version
     */
    private function checkUserVersionAction(Version $version)
    {
        if (null === $this->getUser() || $this->getUser()->getId() != $version->getPresentation()->getUser()->getId()) {
            throw $this->createAccessDeniedException('You can not access this presentation.');
        }
    }
}
