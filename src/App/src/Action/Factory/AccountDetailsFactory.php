<?php
namespace App\Action\Factory;

use Interop\Container\ContainerInterface;

use App\Action;

class AccountDetailsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Action\AccountDetailsAction(
            $container->get(\App\Service\Refund\Data\BankDetailsHandler::class)
        );
    }
}
