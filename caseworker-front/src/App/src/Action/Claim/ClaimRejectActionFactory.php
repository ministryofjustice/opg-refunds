<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimRejectActionFactory
 * @package App\Action\Claim
 */
class ClaimRejectActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimRejectAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimRejectAction(
            $container->get(ClaimService::class)
        );
    }
}
