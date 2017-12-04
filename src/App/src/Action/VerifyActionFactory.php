<?php

namespace App\Action;

use App\Service\Refund\Refund as RefundService;
use Interop\Container\ContainerInterface;

/**
 * Class VerifyActionFactory
 * @package App\Action
 */
class VerifyActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return VerifyAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new VerifyAction(
            $container->get(RefundService::class)
        );
    }
}