<?php

namespace App\View\Date;

use Interop\Container\ContainerInterface;

/**
 * Class DateFormatterFactory
 * @package App\View\Date
 */
class DateFormatterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DateFormatter(
            $container->get(IDateProvider::class)
        );
    }
}
