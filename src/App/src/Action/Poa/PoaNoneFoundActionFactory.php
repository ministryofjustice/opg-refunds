<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaNoneFoundActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}