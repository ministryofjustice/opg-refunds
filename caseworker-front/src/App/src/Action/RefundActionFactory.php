<?php

namespace App\Action;

use App\Service\Refund\Refund as RefundService;
use Interop\Container\ContainerInterface;

class RefundActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return RefundAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new RefundAction(
            $container->get(RefundService::class)
        );
    }
}
