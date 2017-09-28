<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaAction(
            $container->get(ClaimService::class)
        );
    }
}