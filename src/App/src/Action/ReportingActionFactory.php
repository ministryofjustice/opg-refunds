<?php

namespace App\Action;

use App\Service\Reporting as ReportingService;
use Interop\Container\ContainerInterface;

/**
 * Class ReportingActionFactory
 * @package App\Action
 */
class ReportingActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ReportingAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ReportingAction(
            $container->get(ReportingService::class)
        );
    }
}