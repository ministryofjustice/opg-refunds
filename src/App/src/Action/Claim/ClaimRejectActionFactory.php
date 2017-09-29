<?php

namespace App\Action\Claim;

use App\Service\Claim as ClaimService;
use App\View\Poa\PoaFormatter;
use Interop\Container\ContainerInterface;

class ClaimRejectActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimRejectAction(
            $container->get(ClaimService::class),
            $container->get(PoaFormatter::class)
        );
    }
}