<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaCancelNoneFoundActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaCancelNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}