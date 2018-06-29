<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimWithdrawActionFactory
 * @package App\Action\Claim
 */
class ClaimWithdrawActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimWithdrawAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimWithdrawAction(
            $container->get(ClaimService::class)
        );
    }
}