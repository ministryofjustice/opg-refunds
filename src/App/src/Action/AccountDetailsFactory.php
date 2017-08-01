<?php
namespace App\Action;

use Interop\Container\ContainerInterface;

class AccountDetailsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new AccountDetailsAction(
            $container->get(\App\Service\Refund\Data\BankDetailsHandler::class),
            $container->get(\App\Service\Refund\ProcessApplication::class)
        );
    }
}
