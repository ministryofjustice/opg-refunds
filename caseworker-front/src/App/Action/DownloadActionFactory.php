<?php

namespace App\Action;

use App\Service\Refund\Refund as RefundService;
use Interop\Container\ContainerInterface;

class DownloadActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return DownloadAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new DownloadAction(
            $container->get(RefundService::class)
        );
    }
}