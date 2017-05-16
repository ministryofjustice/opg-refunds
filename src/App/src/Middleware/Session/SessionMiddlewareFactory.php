<?php
namespace App\Middleware\Session;

use Interop\Container\ContainerInterface;

class SessionMiddlewareFactory
{

    public function __invoke(ContainerInterface $container)
    {

        $sessionTTL =  $container->get( 'config' )['session']['ttl'];

        return new SessionMiddleware(
            $container->get( \App\Service\Session\SessionManager::class ), $sessionTTL
        );
    }

}