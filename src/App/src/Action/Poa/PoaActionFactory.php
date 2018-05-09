<?php

namespace App\Action\Poa;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class PoaActionFactory
 * @package App\Action\Poa
 */
class PoaActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PoaAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new PoaAction(
            $container->get(ClaimService::class)
        );
    }
}