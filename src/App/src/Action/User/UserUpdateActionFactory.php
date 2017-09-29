<?php

namespace App\Action\User;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

class UserUpdateActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new UserUpdateAction(
            $container->get(UserService::class)
        );
    }
}