<?php

namespace App;

use Zend\Crypt\PublicKey\Rsa;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                //  Actions
                Action\ClaimAction::class => Action\ClaimActionFactory::class,
                Action\UserAction::class => Action\UserActionFactory::class,
                Action\CaseworkerClaimAction::class => Action\CaseworkerClaimActionFactory::class,
                Action\PingAction::class => Action\PingActionFactory::class,
                Action\SpreadsheetAction::class => Action\SpreadsheetActionFactory::class,

                // Middleware

                //  Services
                Service\Claim::class => Service\ClaimFactory::class,
                Service\User::class => Service\UserFactory::class,
                Service\Spreadsheet::class => Service\SpreadsheetFactory::class,

                //Crypt
                Crypt\Hybrid::class => Crypt\HybridFactory::class,
                Rsa::class => Crypt\RsaFactory::class,
            ],
        ];
    }

}
