<?php

namespace App\Service\Date;

use Interop\Container\ContainerInterface;

/**
 * Class DateFormatterPlatesExtensionFactory
 * @package App\Service\Date
 */
class DateFormatterPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DateFormatterPlatesExtension(
            $container->get(DateFormatter::class)
        );
    }
}
