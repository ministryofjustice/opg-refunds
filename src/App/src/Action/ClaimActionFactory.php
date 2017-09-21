<?php

namespace App\Action;

use App\Service\Claim;
use Applications\Service\DataMigration;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimActionFactory
 * @package App\Action
 */
class ClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimAction(
            $container->get(Claim::class),
            $container->get(DataMigration::class)
        );
    }
}