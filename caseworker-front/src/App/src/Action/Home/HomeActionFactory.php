<?php

namespace App\Action\Home;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class HomeActionFactory
 * @package App\Action\Home
 */
class HomeActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return HomeAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new HomeAction(
            $container->get(UserService::class)
        );
    }
}
