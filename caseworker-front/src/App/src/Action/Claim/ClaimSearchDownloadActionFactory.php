<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use App\Service\User\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimSearchDownloadActionFactory
 * @package App\Action\Claim
 */
class ClaimSearchDownloadActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimSearchDownloadAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimSearchDownloadAction(
            $container->get(ClaimService::class)
        );
    }
}
