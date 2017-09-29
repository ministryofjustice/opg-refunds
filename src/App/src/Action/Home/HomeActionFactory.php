<?php

namespace App\Action\Home;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

class HomeActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new HomeAction(
            $container->get(UserService::class)
        );
    }
}