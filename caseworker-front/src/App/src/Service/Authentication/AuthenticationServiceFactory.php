<?php

namespace App\Service\Authentication;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationService(
            null,   //  Use default storage
            $container->get(AuthenticationAdapter::class)
        );
    }
}
