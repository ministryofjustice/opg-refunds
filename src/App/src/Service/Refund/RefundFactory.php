<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 03/10/17
 * Time: 15:28
 */

namespace App\Service\Refund;

use App\Service\Date\IDate as DateService;
use Interop\Container\ContainerInterface;

/**
 * Class RefundFactory
 * @package App\Service\Refund
 */
class RefundFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Refund(
            $container->get(DateService::class)
        );
    }
}