<?php

namespace App\Action\Password;

use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class PasswordResetActionFactory
 * @package App\Action\Password
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
            $container->get(UserService::class),
            $container->get(\Alphagov\Notifications\Client::class)
        );
    }
}
