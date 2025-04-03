<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class LandingRedirectAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * Starts the authentication scheme.
     *
     * @param Request $request
     * @param \Throwable|null $authException
     * @return Response
     */
    public function start(Request $request, \Throwable $authException = null): Response
    {
        return new RedirectResponse('/landing');
    }
}
