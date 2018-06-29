<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimContactDetailsActionFactory
 * @package App\Action\Claim
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