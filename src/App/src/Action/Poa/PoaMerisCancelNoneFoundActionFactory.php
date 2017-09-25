<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaMerisCancelNoneFoundActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaMerisCancelNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}