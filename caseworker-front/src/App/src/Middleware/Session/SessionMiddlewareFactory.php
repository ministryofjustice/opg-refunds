<?php

namespace App\Middleware\Session;

use Interop\Container\ContainerInterface;
use Laminas\Session\SessionManager;

/**
 * Class SessionMiddlewareFactory
 * @package App\Middleware\Session
 */
class SessionMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return SessionMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        return new SessionMiddleware(
            $container->get(SessionManager::class)
        );
    }
}
