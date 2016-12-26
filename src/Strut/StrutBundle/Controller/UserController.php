<?php

namespace Strut\StrutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    /**
     * @Route("/user-locale", name="get-user-locale")
     * @return JsonResponse
     */
    public function getUserLocaleAction()
    {
        if ($this->getUser()) {
            $lang = $this->getUser()->getConfig()->getLanguage();
            return new JsonResponse($lang);
        }
        return new JsonResponse([], 401);
    }

    /**
     * @Route("/user-locale/", name="set-user-locale")
     * @param Request $request
     * @return JsonResponse
     */
    public function setUserLocaleAction(Request $request)
    {
        if ($this->getUser()) {
            $lang = $request->get('lang');
            $this->getUser()->getConfig()->setLanguage($lang);
            return new JsonResponse($lang);
        }
        return new JsonResponse([], 401);
    }
}
