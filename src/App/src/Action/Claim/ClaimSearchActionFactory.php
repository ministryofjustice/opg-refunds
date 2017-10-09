<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimSearchActionFactory
 * @package App\Action\Claim
 */
class ClaimSearchActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimSearchAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimSearchAction(
            $container->get(ClaimService::class)
        );
    }
}
