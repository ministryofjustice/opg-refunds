<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimPoaActionFactory
 * @package App\Action
 */
class ClaimPoaActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimPoaAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimPoaAction(
            $container->get(ClaimService::class),
            $container->get(UserService::class)
        );
    }
}