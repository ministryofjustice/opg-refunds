<?php

namespace App\Middleware\Auth;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Expressive\Helper\UrlHelper;

/**
 * Class AuthenticationMiddlewareFactory
 * @package App\Middleware\Session
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
            $container->get(AuthenticationService::class),
            $container->get(UrlHelper::class)
        );
    }
}
