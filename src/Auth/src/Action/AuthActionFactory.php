<?php

namespace Auth\Action;

use Auth\Service\AuthenticationService;
use Interop\Container\ContainerInterface;

/**
 * Class AuthActionFactory
 * @package Auth\Action
 */
class AuthActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthAction(
            $container->get(AuthenticationService::class)
        );
    }
}
