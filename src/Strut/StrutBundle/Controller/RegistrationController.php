<?php

namespace Strut\StrutBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

final class RegistrationController
{
    public function register(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');

        // validation logic.
        // ...

        // Persist the user in your database.
        $this->registrationService->register($email, $password);

        $credentials = [
            '_csrf_token' => 'generate-a-csrf-token',
            '_username' => $email,
            '_password' => $password,
        ];

        // Create a uri to the login authentication route.
        $uri = $request->getUriForPath('/login/check');

        // Create a sub request which pretends to be a user login request.
        $loginRequest = Request::create(
            $uri,
            'POST',
            $credentials,
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all()
        );

        // Let our application handle the login request.
        return $app->handle($loginRequest);
    }
}