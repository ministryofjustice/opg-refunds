<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

/**
 * Class SignInActionFactory
 * @package App\Action
 */
class SignInActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return SignInAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new SignInAction($container->get(AuthenticationService::class));
    }
}
