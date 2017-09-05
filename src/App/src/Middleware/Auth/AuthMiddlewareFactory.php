<?php

namespace App\Middleware\Auth;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper;

/**
 * Class AuthMiddlewareFactory
 * @package App\Middleware\Session
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
            $container->get(UrlHelper::class)
        );
    }
}
