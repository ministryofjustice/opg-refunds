<?php
namespace App\Action\Factory;

use Interop\Container\ContainerInterface;

use App\Action;

class SummaryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Action\SummaryAction(
            $container->get(\App\Service\Refund\ProcessApplication::class),
            $container->get(\App\Service\Refund\Beta\BetaLinkChecker::class)
        );
    }
}
