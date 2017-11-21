<?php

namespace App\Action;

use App\Service\Reporting as ReportingService;
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
            $container->get(ReportingService::class)
        );
    }
}