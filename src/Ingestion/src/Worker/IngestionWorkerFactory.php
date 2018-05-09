<?php

namespace Ingestion\Worker;

use Ingestion\Service\ApplicationIngestion;
use Interop\Container\ContainerInterface;

class IngestionWorkerFactory
{
    /**
     * @param ContainerInterface $container
     * @return IngestionWorker
     */
    public function __invoke(ContainerInterface $container)
    {
        return new IngestionWorker(
            $container->get(ApplicationIngestion::class)
        );
    }
}
