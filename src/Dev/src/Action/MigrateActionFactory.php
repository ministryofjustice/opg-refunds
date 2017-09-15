<?php

namespace Dev\Action;

use Applications\Service\DataMigration;
use Interop\Container\ContainerInterface;

class MigrateActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new MigrateAction(
            $container->get(DataMigration::class)
        );
    }
}