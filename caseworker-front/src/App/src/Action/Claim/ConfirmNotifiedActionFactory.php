<?php

namespace App\Action\Claim;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ConfirmNotifiedActionFactory
 * @package App\Action\Claim
 */
class ConfirmNotifiedActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ConfirmNotifiedAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ConfirmNotifiedAction(
            $container->get(ClaimService::class)
        );
    }
}