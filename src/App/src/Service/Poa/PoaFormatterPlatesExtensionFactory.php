<?php

namespace App\Service\Poa;

use Interop\Container\ContainerInterface;

/**
 * Class PoaFormatterPlatesExtensionFactory
 * @package App\Service\Poa
 */
class PoaFormatterPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaFormatterPlatesExtension(
            $container->get(PoaFormatter::class)
        );
    }
}