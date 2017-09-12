<?php

namespace Applications\Service;

use App\Crypt\Hybrid as HybridCipher;
use Interop\Container\ContainerInterface;

class DataMigrationFactory
{
    /**
     * @param ContainerInterface $container
     * @return DataMigration
     */
    public function __invoke(ContainerInterface $container)
    {
        return new DataMigration(
            $container->get('doctrine.entity_manager.orm_applications'),
            $container->get(HybridCipher::class)
        );
    }
}