<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimChangeOutcomeActionFactory
 * @package App\Action\Claim
 */
class ClaimChangeOutcomeActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimChangeOutcomeAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimChangeOutcomeAction(
            $container->get(ClaimService::class)
        );
    }
}
