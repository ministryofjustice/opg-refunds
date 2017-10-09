<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimAssignActionFactory
 * @package App\Action\Claim
 */
class ClaimAssignActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimAssignAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimAssignAction(
            $container->get(ClaimService::class)
        );
    }
}