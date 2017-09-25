<?php

namespace App;

use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;

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
            'templates'    => $this->getTemplates(),
            'rbac'         => include __DIR__ . '/../config/rbac.php',
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
                Action\AdminAction::class => Action\AdminAction::class,
                Action\CaseworkerAction::class => Action\CaseworkerAction::class,
                Action\CsvDownloadAction::class => Action\CsvDownloadAction::class,
                Action\DownloadAction::class => Action\DownloadAction::class,
                Action\HomePageAction::class => Action\HomePageAction::class,
                Action\PasswordRequestResetAction::class => Action\PasswordRequestResetAction::class,
                Action\PasswordSetNewAction::class => Action\PasswordSetNewAction::class,
                Action\RefundAction::class => Action\RefundAction::class,
                Action\ReportingAction::class => Action\ReportingAction::class,

                Service\Date\IDateProvider::class => Service\Date\DateProvider::class,
                Service\Claim::class => Service\Claim::class,
            ],
            'factories'  => [
                //  Actions
                Action\SignInAction::class => Action\SignInActionFactory::class,
                Action\SignOutAction::class => Action\SignOutActionFactory::class,
                Action\ProcessNewClaimAction::class => Action\ProcessNewClaimActionFactory::class,
                Action\ClaimAction::class => Action\ClaimActionFactory::class,

                // Middleware
                Middleware\Auth\AuthorizationMiddleware::class => Middleware\Auth\AuthorizationMiddlewareFactory::class,
                Middleware\Session\SessionMiddleware::class => Middleware\Session\SessionMiddlewareFactory::class,

                // Services
                Service\Auth\AuthAdapter::class => Service\Auth\AuthAdapterFactory::class,
                AuthenticationService::class => Service\Auth\AuthenticationServiceFactory::class,
                SessionManager::class => Service\Session\SessionManagerFactory::class,
                Service\Date\DateFormatter::class => Service\Date\DateFormatterFactory::class,
                Service\ErrorMapper\ErrorMapper::class => Service\ErrorMapper\ErrorMapperFactory::class,

                // View Helper
                Service\Date\DateFormatterPlatesExtension::class => Service\Date\DateFormatterPlatesExtensionFactory::class,
                Service\ErrorMapper\ErrorMapperPlatesExtension::class => Service\ErrorMapper\ErrorMapperPlatesExtensionFactory::class,
            ],
            'initializers' => [
                Action\Initializers\UrlHelperInitializer::class,
                Action\Initializers\TemplatingSupportInitializer::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'app'    => [__DIR__ . '/../templates/app'],
                'error'  => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
                'snippet' => [__DIR__ . '/../templates/snippet'],
            ],
        ];
    }
}
