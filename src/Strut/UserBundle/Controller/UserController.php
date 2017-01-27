<?php

namespace Strut\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    /**
     * @Route("/locale", name="get-user-locale")
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
}
