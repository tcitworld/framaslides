<?php

namespace Strut\StrutBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/app", name="app")
     * @return Response
     */
    public function appAction(): Response
    {
        return $this->render('@Strut/index.html');
    }
}
