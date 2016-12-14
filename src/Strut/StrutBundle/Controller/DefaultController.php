<?php

namespace Strut\StrutBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/app", name="app")
     * @param Request $request
     * @return Response
     */
    public function appAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('@Strut/index.html');
    }
}
