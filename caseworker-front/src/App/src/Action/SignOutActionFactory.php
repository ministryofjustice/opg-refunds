<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;

/**
 * Class SignOutActionFactory
 * @package App\Action
 */
class SignOutActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return SignOutAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new SignOutAction(
            $container->get(AuthenticationService::class),
            $container->get(SessionManager::class)
        );
    }
}
