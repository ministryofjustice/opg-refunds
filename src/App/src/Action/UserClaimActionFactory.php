<?php

namespace App\Action;

use App\Service\Claim;
use Ingestion\Service\DataMigration;
use Interop\Container\ContainerInterface;

/**
 * Class UserClaimActionFactory
 * @package App\Action
 */
class UserClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new UserClaimAction(
            $container->get(Claim::class)
        );
    }
}
