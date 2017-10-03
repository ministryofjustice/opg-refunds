<?php

namespace App\Service\Poa;

use App\Service\Refund\Refund as RefundService;
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
            $container->get(RefundService::class)
        );
    }
}
