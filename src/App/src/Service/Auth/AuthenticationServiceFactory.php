<?php

namespace App\Service\Auth;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationService(
            null,   //  The session will be added in the action
            $container->get(AuthAdapter::class)
        );
    }
}
