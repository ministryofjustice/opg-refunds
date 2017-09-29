<?php

namespace App\View\Poa;

use App\Service\Poa\PoaFormatter as PoaFormatterService;
use Interop\Container\ContainerInterface;

/**
 * Class PoaFormatterPlatesExtensionFactory
 * @package App\View\Poa
 */
class PoaFormatterPlatesExtensionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaFormatterPlatesExtension(
            $container->get(PoaFormatterService::class)
        );
    }
}