<?php

namespace App\View\Date;

use App\Service\Date\IDate as DateService;
use Interop\Container\ContainerInterface;

class DateFormatterPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DateFormatterPlatesExtension(
            $container->get(DateService::class)
        );
    }
}