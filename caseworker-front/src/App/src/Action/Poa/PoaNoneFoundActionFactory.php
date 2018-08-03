<?php

namespace App\Action\Poa;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class PoaNoneFoundActionFactory
 * @package App\Action\Poa
 */
class PoaNoneFoundActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PoaNoneFoundAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new PoaNoneFoundAction(
            $container->get(ClaimService::class)
        );
    }
}
