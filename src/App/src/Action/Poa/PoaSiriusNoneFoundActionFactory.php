<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaSiriusNoneFoundActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaSiriusNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}