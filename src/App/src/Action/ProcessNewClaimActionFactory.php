<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ProcessNewClaimActionFactory
 * @package App\Action
 */
class ProcessNewClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ProcessNewClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ProcessNewClaimAction(
            $container->get(ClaimService::class)
        );
    }
}
