<?php

namespace App\Action;

use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class PasswordResetActionFactory
 * @package App\Action
 */
class PasswordResetActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PasswordResetAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new PasswordResetAction(
            $container->get(UserService::class)
        );
    }
}
