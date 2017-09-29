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
                Action\RefundAction::class => Action\RefundAction::class,
                Action\ReportingAction::class => Action\ReportingAction::class,
                Action\PasswordRequestResetAction::class => Action\PasswordRequestResetAction::class,
                Action\PasswordSetNewAction::class => Action\PasswordSetNewAction::class,

                View\Date\IDateProvider::class => View\Date\DateProvider::class,
                View\Details\DetailsFormatter::class => View\Details\DetailsFormatter::class,
                View\Poa\PoaFormatter::class => View\Poa\PoaFormatter::class,
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
                Middleware\Authorization\AuthorizationMiddleware::class => Middleware\Authorization\AuthorizationMiddlewareFactory::class,
                Middleware\Session\SessionMiddleware::class => Middleware\Session\SessionMiddlewareFactory::class,

                // Services
                Service\Authentication\AuthenticationAdapter::class => Service\Authentication\AuthenticationAdapterFactory::class,
                AuthenticationService::class => Service\Authentication\AuthenticationServiceFactory::class,
                SessionManager::class => Service\Session\SessionManagerFactory::class,
                View\Date\DateFormatter::class => View\Date\DateFormatterFactory::class,
                View\ErrorMapper\ErrorMapper::class => View\ErrorMapper\ErrorMapperFactory::class,

                // View Helper
                View\Date\DateFormatterPlatesExtension::class => View\Date\DateFormatterPlatesExtensionFactory::class,
                View\Details\DetailsFormatterPlatesExtension::class => View\Details\DetailsFormatterPlatesExtensionFactory::class,
                View\ErrorMapper\ErrorMapperPlatesExtension::class => View\ErrorMapper\ErrorMapperPlatesExtensionFactory::class,
                View\Poa\PoaFormatterPlatesExtension::class => View\Poa\PoaFormatterPlatesExtensionFactory::class,
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
