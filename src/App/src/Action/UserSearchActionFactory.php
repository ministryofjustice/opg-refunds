<?php

namespace App\Action;

use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class UserSearchActionFactory
 * @package App\Action
 */
class UserSearchActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserSearchAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UserSearchAction(
            $container->get(UserService::class)
        );
    }
}