<?php

namespace App\Action;

use App\Service\RefundCase;
use Applications\Service\DataMigration;
use Interop\Container\ContainerInterface;

/**
 * Class RefundCaseActionFactory
 * @package App\Action
 */
class RefundCaseActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return RefundCaseAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new RefundCaseAction(
            $container->get(RefundCase::class),
            $container->get(DataMigration::class)
        );
    }
}