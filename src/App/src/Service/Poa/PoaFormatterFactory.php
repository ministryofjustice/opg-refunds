<?php

namespace App\Service\Poa;

use App\Service\Date\IDateProvider;
use Interop\Container\ContainerInterface;

/**
 * Class PoaFormatterFactory
 * @package App\Service\Poa
 */
class PoaFormatterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PoaFormatter(
            $container->get(IDateProvider::class)
        );
    }
}
