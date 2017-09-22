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
                Action\CsvDownloadAction::class => Action\CsvDownloadAction::class,
                Action\User\UserAction::class => Action\User\UserAction::class,
                Action\User\UserUpdateAction::class => Action\User\UserUpdateAction::class,
                Action\DownloadAction::class => Action\DownloadAction::class,
                Action\HomePageAction::class => Action\HomePageAction::class,
                Action\HomeRefundAction::class => Action\HomeRefundAction::class,
                Action\HomeReportingAction::class => Action\HomeReportingAction::class,
                Action\PasswordRequestResetAction::class => Action\PasswordRequestResetAction::class,
                Action\PasswordSetNewAction::class => Action\PasswordSetNewAction::class,

                Service\Date\IDateProvider::class => Service\Date\DateProvider::class,
                Service\Details\DetailsFormatter::class => Service\Details\DetailsFormatter::class,
                Service\Poa\PoaFormatter::class => Service\Poa\PoaFormatter::class,
                Service\Claim::class => Service\Claim::class,
            ],
            'factories'  => [
                //  Actions
                Action\SignInAction::class => Action\SignInActionFactory::class,
                Action\SignOutAction::class => Action\SignOutActionFactory::class,
                Action\ClaimAction::class => Action\ClaimActionFactory::class,
                Action\Poa\PoaNoneFoundAction::class => Action\Poa\PoaNoneFoundActionFactory::class,
                Action\Poa\PoaAction::class => Action\Poa\PoaActionFactory::class,
                Action\Poa\PoaDeleteAction::class => Action\Poa\PoaDeleteActionFactory::class,
                Action\Claim\ClaimAcceptAction::class => Action\Claim\ClaimAcceptActionFactory::class,
                Action\Claim\ClaimRejectAction::class => Action\Claim\ClaimRejectActionFactory::class,

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
                Service\Details\DetailsFormatterPlatesExtension::class => Service\Details\DetailsFormatterPlatesExtensionFactory::class,
                Service\ErrorMapper\ErrorMapperPlatesExtension::class => Service\ErrorMapper\ErrorMapperPlatesExtensionFactory::class,
                Service\Poa\PoaFormatterPlatesExtension::class => Service\Poa\PoaFormatterPlatesExtensionFactory::class,
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
