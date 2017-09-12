<?php

namespace App\Action;

use Applications\Service\DataMigration;
use Interop\Container\ContainerInterface;

class CasesActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return CasesAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new CasesAction(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(DataMigration::class)
        );
    }
}