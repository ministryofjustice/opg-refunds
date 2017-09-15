<?php

namespace App\Action;

use App\Service\Cases;
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
            $container->get(Cases::class),
            $container->get(DataMigration::class)
        );
    }
}