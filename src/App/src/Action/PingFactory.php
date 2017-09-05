<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class PingFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $authEntityManager = $container->get('doctrine.entity_manager.orm_auth');
        $casesEntityManager = $container->get('doctrine.entity_manager.orm_cases');

        return new PingAction($authEntityManager, $casesEntityManager);
    }
}