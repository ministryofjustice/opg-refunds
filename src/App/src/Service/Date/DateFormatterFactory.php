<?php

namespace App\Service\Date;

use Interop\Container\ContainerInterface;

/**
 * Class DateFormatterFactory
 * @package App\Service\Date
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
