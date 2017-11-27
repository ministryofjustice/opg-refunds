<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimReassignActionFactory
 * @package App\Action\Claim
 */
class ClaimReassignActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimReassignAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimReassignAction(
            $container->get(ClaimService::class)
        );
    }
}