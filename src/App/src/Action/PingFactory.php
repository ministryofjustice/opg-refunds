<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class PingFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.entity_manager.orm_cases');

        return new PingAction($entityManager);
    }
}