<?php

namespace App;

use Alphagov\Notifications\Client as NotifyClient;
use Aws\Kms\KmsClient;
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
            'doctrine'     => include __DIR__ . '/../config/doctrine.php',
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
                //  Services
                Service\TokenGenerator::class => Service\TokenGenerator::class,

                // Middleware
                Middleware\ProblemDetailsMiddleware::class => Middleware\ProblemDetailsMiddleware::class,
            ],
            'factories'  => [
                'doctrine.entity_manager.orm_cases' => [\ContainerInteropDoctrine\EntityManagerFactory::class, 'orm_cases'],
                'doctrine.entity_manager.orm_cases_migration' => [\ContainerInteropDoctrine\EntityManagerFactory::class, 'orm_cases_migration'],
                'doctrine.entity_manager.orm_sirius' => [\ContainerInteropDoctrine\EntityManagerFactory::class, 'orm_sirius'],
                'doctrine.entity_manager.orm_sirius_migration' => [\ContainerInteropDoctrine\EntityManagerFactory::class, 'orm_sirius_migration'],

                //  Actions
                Action\ClaimAction::class => Action\ClaimActionFactory::class,
                Action\ClaimNoteAction::class => Action\ClaimNoteActionFactory::class,
                Action\ClaimPoaAction::class => Action\ClaimPoaActionFactory::class,
                Action\ClaimSearchAction::class => Action\ClaimSearchActionFactory::class,
                Action\ClaimSearchDownloadAction::class => Action\ClaimSearchDownloadActionFactory::class,
                Action\NotifyAction::class => Action\NotifyActionFactory::class,
                Action\PasswordResetAction::class => Action\PasswordResetActionFactory::class,
                Action\PingAction::class => Action\PingActionFactory::class,
                Action\ReportingAction::class => Action\ReportingActionFactory::class,
                Action\SpreadsheetAction::class => Action\SpreadsheetActionFactory::class,
                Action\UserAction::class => Action\UserActionFactory::class,
                Action\UserClaimAction::class => Action\UserClaimActionFactory::class,
                Action\UserSearchAction::class => Action\UserSearchActionFactory::class,

                //  Services
                NotifyClient::class => Service\NotifyClientFactory::class,
                Service\Claim::class => Service\ClaimFactory::class,
                Service\Notify::class => Service\NotifyFactory::class,
                Service\User::class => Service\UserFactory::class,
                Service\Reporting::class => Service\ReportingFactory::class,
                Service\Spreadsheet::class => Service\SpreadsheetFactory::class,
                Service\PoaLookup::class => Service\PoaLookupFactory::class,

                //Crypt
                Crypt\Hybrid::class => Crypt\HybridFactory::class,
                Rsa::class => Crypt\RsaFactory::class,
                KmsClient::class => Crypt\AwsKmsFactory::class,
            ],
        ];
    }

}
