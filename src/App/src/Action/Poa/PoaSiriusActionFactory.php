<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaSiriusActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaSiriusAction(
            $container->get(ClaimService::class)
        );
    }
}