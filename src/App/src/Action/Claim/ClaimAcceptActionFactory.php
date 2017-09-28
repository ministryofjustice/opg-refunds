<?php

namespace App\Action\Claim;

use App\Service\Claim as ClaimService;
use App\Service\Poa\PoaFormatter;
use Interop\Container\ContainerInterface;

class ClaimAcceptActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimAcceptAction(
            $container->get(ClaimService::class),
            $container->get(PoaFormatter::class)
        );
    }
}