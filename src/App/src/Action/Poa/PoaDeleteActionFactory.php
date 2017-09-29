<?php

namespace App\Action\Poa;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaDeleteActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaDeleteAction(
            $container->get(ClaimService::class)
        );
    }
}