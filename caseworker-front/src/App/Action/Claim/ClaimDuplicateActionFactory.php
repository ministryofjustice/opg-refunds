<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimDuplicateActionFactory
 * @package App\Action\Claim
 */
class ClaimDuplicateActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimDuplicateAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimDuplicateAction(
            $container->get(ClaimService::class)
        );
    }
}
