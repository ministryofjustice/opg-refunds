<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

/**
 * Class PingActionFactory
 * @package App\Action
 */
class PingActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return PingAction
     */
    public function __invoke(ContainerInterface $container)
    {
        $casesEntityManager = $container->get('doctrine.entity_manager.orm_cases');
        $siriusEntityManager = $container->get('doctrine.entity_manager.orm_sirius');

        return new PingAction($casesEntityManager, $siriusEntityManager);
    }
}