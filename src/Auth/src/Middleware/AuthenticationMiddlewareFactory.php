<?php

namespace Auth\Middleware;

use Auth\Service\Authentication;
use Interop\Container\ContainerInterface;

/**
 * Class AuthenticationMiddlewareFactory
 * @package Auth\Middleware
 */
class AuthenticationMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationMiddleware(
            $container->get(Authentication::class)
        );
    }
}
