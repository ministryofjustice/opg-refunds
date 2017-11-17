<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

/**
 * Class StatsActionFactory
 * @package App\Action
 */
class StatsActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return StatsAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new StatsAction(
            $container->get('doctrine.entity_manager.orm_cases')
        );
    }
}