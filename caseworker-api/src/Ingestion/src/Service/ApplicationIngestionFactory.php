<?php

namespace Ingestion\Service;

use Interop\Container\ContainerInterface;

class ApplicationIngestionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ApplicationIngestion
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ApplicationIngestion(
            $container->get('doctrine.entity_manager.orm_applications'),
            $container->get('doctrine.entity_manager.orm_cases')
        );
    }
}
