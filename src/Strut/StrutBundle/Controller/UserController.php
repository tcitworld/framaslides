<?php

namespace Strut\StrutBundle\Controller;

use Strut\StrutBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Strut\StrutBundle\Entity\Presentation;
use Strut\StrutBundle\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller {

    /**
     * @route("/user-locale/", name="user-locale")
     * @return JsonResponse
     */
    public function getUserLocale(/*User $user*/) {
        return new JsonResponse('fr');
    }
}
