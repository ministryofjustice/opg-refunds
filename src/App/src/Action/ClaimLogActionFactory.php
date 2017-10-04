<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimLogActionFactory
 * @package App\Action
 */
class ClaimLogActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimLogAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimLogAction(
            $container->get(ClaimService::class),
            $container->get(UserService::class)
        );
    }
}