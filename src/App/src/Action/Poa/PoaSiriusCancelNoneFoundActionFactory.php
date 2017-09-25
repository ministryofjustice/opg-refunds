<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaSiriusCancelNoneFoundActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaSiriusCancelNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}