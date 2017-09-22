<?php

namespace Auth\Middleware;

use Auth\Service\Authentication;
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
            $container->get(Authentication::class)
        );
    }
}
