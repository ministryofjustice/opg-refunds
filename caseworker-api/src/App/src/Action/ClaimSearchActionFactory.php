<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimSearchActionFactory
 * @package App\Action
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
