<?php

namespace App\Action\Password;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class PasswordChangeActionFactory
 * @package App\Action\Password
 */
class PasswordChangeActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PasswordChangeAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new PasswordChangeAction(
            $container->get(UserService::class)
        );
    }
}