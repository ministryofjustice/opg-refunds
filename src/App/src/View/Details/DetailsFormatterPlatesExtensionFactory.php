<?php

namespace App\View\Details;

use Interop\Container\ContainerInterface;

/**
 * Class DetailsFormatterPlatesExtensionFactory
 * @package App\View\Details
 */
class DetailsFormatterPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DetailsFormatterPlatesExtension(
            $container->get(DetailsFormatter::class)
        );
    }
}