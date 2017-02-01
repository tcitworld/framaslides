<?php

namespace Strut\StrutBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Strut\StrutBundle\Entity\Picture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class PictureController extends Controller
{
    /**
     * @Route("/picture", name="new-picture")
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPictureAction(Request $request)
    {
        $presentationTitle = $request->get('presentation');

        $em = $this->getDoctrine()->getManager();

        $repository = $this->get('strut.presentation_repository');
        $presentation = $repository->findOneBy([
            'user' => $this->getUser(),
            'title' => $presentationTitle
        ]);

        if (!$request->files->has('file')) {
            return new JsonResponse(['error' => 'File not passed'], 400);
        }
        $pictureFile = $request->files->get('file');

        $picture = new Picture($presentation);

        $extension = $pictureFile->guessExtension();
        $picture->setExtension($extension);
        $picture->setFileName($pictureFile->getClientOriginalName());

        $pictureFile->move($this->getParameter('pictures_directory'), $picture->getUuid() . '.' . $picture->getExtension());


        $em->persist($picture);
        $em->flush();

        $json = $this->get('jms_serializer')->serialize($picture, 'json');
        return (new JsonResponse())->setJson($json);
    }

    /**
     * @Route("/picture/{uuid}", name="show-picture")
     * @param Picture $picture
     * @return BinaryFileResponse
     */
    public function showPictureAction(Picture $picture)
    {
        return new BinaryFileResponse($this->getParameter('pictures_directory') . '/' . $picture->getUuid() . '.' . $picture->getExtension());
    }

    /**
     * @Route("pictures/{page}", name="pictures", defaults={"page" = "1"})
     * @param int $page
     * @return RedirectResponse|Response
     */
    public function showPicturesAction(int $page)
    {
        $repository = $this->get('strut.picture_repository');
        $pictures = $repository->getPictures($this->getUser());

        $pagerAdapter = new DoctrineORMAdapter($pictures->getQuery(), true, false);
        $pagerFanta = new Pagerfanta($pagerAdapter);
        $pagerFanta->setMaxPerPage(12);

        try {
            $pagerFanta->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException $e) {
            if ($page > 1) {
                return $this->redirect($this->generateUrl('pictures', ['page' => $pagerFanta->getNbPages()]), 302);
            }
        }


        return $this->render('default/pictures.html.twig', [
            'pictures' => $pagerFanta,
            'path' => Picture::PATH,
        ]);
    }

    /**
     * @Route("delete-picture/{uuid}", name="delete-picture")
     * @param Picture $picture
     * @return RedirectResponse
     */
    public function deletePictureAction(Picture $picture)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($picture);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'flashes.picture.notice.deleted'
        );

        return $this->redirectToRoute('pictures');
    }
}
