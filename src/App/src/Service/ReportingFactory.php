<?php

namespace App\Service;

use Ingestion\Service\ApplicationIngestion;
use Interop\Container\ContainerInterface;

/**
 * Class ReportingFactory
 * @package App\Service
 */
class ReportingFactory
{
    /**
     * @param ContainerInterface $container
     * @return Reporting
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        return new Reporting(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(ApplicationIngestion::class)
        );
    }
}