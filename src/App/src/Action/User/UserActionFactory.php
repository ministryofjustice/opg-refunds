<?php

namespace App\Action\User;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

class UserActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new UserAction(
            $container->get(UserService::class)
        );
    }
}