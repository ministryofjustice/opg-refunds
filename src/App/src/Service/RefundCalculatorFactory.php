<?php

namespace App\Service;

use App\Service\TimeDate as TimeDateService;
use Interop\Container\ContainerInterface;

/**
 * Class RefundCalculatorFactory
 * @package App\Service\Refund
 */
class RefundCalculatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new RefundCalculator(
            $container->get(TimeDateService::class)
        );
    }
}