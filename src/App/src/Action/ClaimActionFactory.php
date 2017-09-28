<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimActionFactory
 * @package App\Action
 */
class ClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimAction(
            $container->get(ClaimService::class),
            $container->get(UserService::class)
        );
    }
}