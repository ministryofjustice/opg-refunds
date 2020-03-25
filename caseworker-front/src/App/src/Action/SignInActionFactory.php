<?php

namespace App\Action;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;

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
        return new SignInAction(
            $container->get(AuthenticationService::class),
            $container->get(UserService::class),
            $container->get(\Alphagov\Notifications\Client::class)
        );
    }
}
