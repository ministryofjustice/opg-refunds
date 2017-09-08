<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class PingActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $casesEntityManager = $container->get('doctrine.entity_manager.orm_cases');
        $siriusEntityManager = $container->get('doctrine.entity_manager.orm_sirius');

        return new PingAction($casesEntityManager, $siriusEntityManager);
    }
}