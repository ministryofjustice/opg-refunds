<?php

namespace Auth\Middleware;

use Auth\Service\AuthenticationService;
use Interop\Container\ContainerInterface;

/**
 * Class AuthMiddlewareFactory
 * @package Auth\Middleware
 */
class AuthMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthMiddleware(
            $container->get(AuthenticationService::class)
        );
    }
}
