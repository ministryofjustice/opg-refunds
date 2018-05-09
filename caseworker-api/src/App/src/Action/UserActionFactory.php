<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class UserActionFactory
 * @package App\Action
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
            $container->get(ClaimService::class)
        );
    }
}
