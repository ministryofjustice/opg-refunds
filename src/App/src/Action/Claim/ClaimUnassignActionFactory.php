<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimUnassignActionFactory
 * @package App\Action\Claim
 */
class ClaimUnassignActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimUnassignAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimUnassignAction(
            $container->get(ClaimService::class)
        );
    }
}