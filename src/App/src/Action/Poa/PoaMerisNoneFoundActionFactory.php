<?php

namespace App\Action\Poa;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

class PoaMerisNoneFoundActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaMerisNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}