<?php

namespace App\View\Date;

use Interop\Container\ContainerInterface;

/**
 * Class DateFormatterPlatesExtensionFactory
 * @package App\View\Date
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
