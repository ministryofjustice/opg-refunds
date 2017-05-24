<?php

namespace App\Action;

use Interop\Container\ContainerInterface;

class SummaryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new SummaryAction(
            $container->get( \App\Service\Refund\ProcessApplication::class )
        );
    }
}
