<?php

namespace App\Action;

use App\Service\Claim;
use Ingestion\Service\DataMigration;
use Interop\Container\ContainerInterface;

/**
 * Class CaseworkerClaimActionFactory
 * @package App\Action
 */
class CaseworkerClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return CaseworkerClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new CaseworkerClaimAction(
            $container->get(Claim::class),
            $container->get(DataMigration::class)
        );
    }
}
