<?php

namespace App\Action\Poa;

use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class PoaDeleteActionFactory
 * @package App\Action\Poa
 */
class PoaDeleteActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PoaDeleteAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new PoaDeleteAction(
            $container->get(ClaimService::class)
        );
    }
}
