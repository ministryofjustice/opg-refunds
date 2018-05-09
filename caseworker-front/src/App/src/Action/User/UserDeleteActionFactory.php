<?php

namespace App\Action\User;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class UserDeleteActionFactory
 * @package App\Action\User
 */
class UserDeleteActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserDeleteAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UserDeleteAction(
            $container->get(UserService::class)
        );
    }
}