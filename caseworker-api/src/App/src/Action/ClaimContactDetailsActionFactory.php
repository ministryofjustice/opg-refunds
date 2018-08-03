<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimContactDetailsActionFactory
 * @package App\Action
 */
class ClaimContactDetailsActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimContactDetailsAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimContactDetailsAction(
            $container->get(ClaimService::class)
        );
    }
}
