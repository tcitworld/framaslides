<?php

namespace Strut\StrutBundle\Controller;

use Strut\StrutBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller {

    /**
     * @route("/user-locale", name="get-user-locale")
     * @return JsonResponse
     */
    public function getUserLocale() {
        if ($this->getUser()) {
            $lang = $this->getUser()->getConfig()->getLang();
            return new JsonResponse($lang);
        }
        return new JsonResponse([], 401);
    }

    /**
     * @route("/user-locale/", name="set-user-locale")
     * @param Request $request
     * @return JsonResponse
     */
    public function setUserLocale(Request $request) {
        if ($this->getUser()) {
            $lang = $request->get('lang');
            $this->getUser()->getConfig()->setLang($lang);
            return new JsonResponse($lang);
        }
        return new JsonResponse([], 401);
    }
}
