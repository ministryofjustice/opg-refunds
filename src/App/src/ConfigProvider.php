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
                Action\CaseworkerAction::class => Action\CaseworkerActionFactory::class,
                Action\PingAction::class => Action\PingActionFactory::class,
                Action\SpreadsheetAction::class => Action\SpreadsheetActionFactory::class,

                // Middleware

                //  Services
                Service\Caseworker::class => Service\CaseworkerFactory::class,

                //Crypt
                Crypt\Hybrid::class => Crypt\HybridFactory::class,
                Rsa::class => Crypt\RsaFactory::class,
            ],
        ];
    }

}
