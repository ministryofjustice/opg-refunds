<?php

namespace App\Service\Refund;

use App\Service\Date\IDate as DateService;
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
            $container->get(DateService::class)
        );
    }
}