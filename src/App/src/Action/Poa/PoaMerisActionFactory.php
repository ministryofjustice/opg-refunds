<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaMerisActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaMerisAction(
            $container->get(ClaimService::class)
        );
    }
}