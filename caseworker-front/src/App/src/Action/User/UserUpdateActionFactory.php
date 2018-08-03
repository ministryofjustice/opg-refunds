<?php

namespace App\Action\User;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class UserUpdateActionFactory
 * @package App\Action\User
 */
class UserUpdateActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserUpdateAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UserUpdateAction(
            $container->get(UserService::class),
            $container->get(\Alphagov\Notifications\Client::class)
        );
    }
}
