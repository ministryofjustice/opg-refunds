<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use App\Service\Poa\PoaFormatter as PoaFormatterService;
use Interop\Container\ContainerInterface;

class ClaimRejectActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimRejectAction(
            $container->get(ClaimService::class),
            $container->get(PoaFormatterService::class)
        );
    }
}