<?php

namespace App\Action;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimSearchDownloadActionFactory
 * @package App\Action
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