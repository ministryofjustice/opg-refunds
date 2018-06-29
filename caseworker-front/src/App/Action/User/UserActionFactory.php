<?php

namespace App\Action\User;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class UserActionFactory
 * @package App\Action\User
 */
class UserActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UserAction(
            $container->get(UserService::class),
            $container->get(\Alphagov\Notifications\Client::class)
        );
    }
}