<?php

namespace App\Middleware\Session;

use Interop\Container\ContainerInterface;
use UnexpectedValueException;

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
        $config =  $container->get('config');

        if (!isset($config['session']['ttl'])) {
            throw new UnexpectedValueException('Session TTL not configured');
        }

        return new SessionMiddleware(
            $container->get(\App\Service\Session\SessionManager::class),
            $config['session']['ttl']
        );
    }
}
