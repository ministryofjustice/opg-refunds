<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimApproveActionFactory
 * @package App\Action\Claim
 */
class ClaimApproveActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimApproveAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimApproveAction(
            $container->get(ClaimService::class)
        );
    }
}