<?php

namespace App\Service\Details;

use Interop\Container\ContainerInterface;

/**
 * Class DetailsFormatterPlatesExtensionFactory
 * @package App\Service\Details
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