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
                //  Actions
                Action\CsvDownloadAction::class => Action\CsvDownloadAction::class,
                Action\DownloadAction::class => Action\DownloadAction::class,
                Action\RefundAction::class => Action\RefundAction::class,
                Action\ReportingAction::class => Action\ReportingAction::class,
                Action\PasswordRequestResetAction::class => Action\PasswordRequestResetAction::class,
                Action\PasswordSetNewAction::class => Action\PasswordSetNewAction::class,

                // Services
                Service\Claim\Claim::class => Service\Claim\Claim::class,
                Service\Date\IDate::class => Service\Date\Date::class,
                Service\Poa\Poa::class => Service\Poa\Poa::class,
                Service\User\User::class => Service\User\User::class,
            ],
            'factories'  => [
                //  Actions
                Action\Claim\ClaimAction::class => Action\Claim\ClaimActionFactory::class,
                Action\Claim\ClaimApproveAction::class => Action\Claim\ClaimApproveActionFactory::class,
                Action\Claim\ClaimRejectAction::class => Action\Claim\ClaimRejectActionFactory::class,
                Action\Home\HomeAction::class => Action\Home\HomeActionFactory::class,
                Action\Poa\PoaAction::class => Action\Poa\PoaActionFactory::class,
                Action\Poa\PoaDeleteAction::class => Action\Poa\PoaDeleteActionFactory::class,
                Action\Poa\PoaNoneFoundAction::class => Action\Poa\PoaNoneFoundActionFactory::class,
                Action\SignInAction::class => Action\SignInActionFactory::class,
                Action\SignOutAction::class => Action\SignOutActionFactory::class,
                Action\User\UserAction::class => Action\User\UserActionFactory::class,
                Action\User\UserDeleteAction::class => Action\User\UserDeleteActionFactory::class,
                Action\User\UserUpdateAction::class => Action\User\UserUpdateActionFactory::class,

                // Middleware
                Middleware\Authorization\AuthorizationMiddleware::class => Middleware\Authorization\AuthorizationMiddlewareFactory::class,
                Middleware\Session\SessionMiddleware::class => Middleware\Session\SessionMiddlewareFactory::class,

                // Services
                Service\Authentication\AuthenticationAdapter::class => Service\Authentication\AuthenticationAdapterFactory::class,
                Service\Refund\Refund::class => Service\Refund\RefundFactory::class,
                AuthenticationService::class => Service\Authentication\AuthenticationServiceFactory::class,
                SessionManager::class => Service\Session\SessionManagerFactory::class,

                // View Helper
                View\Date\DateFormatterPlatesExtension::class => View\Date\DateFormatterPlatesExtensionFactory::class,
                View\Poa\PoaFormatterPlatesExtension::class => View\Poa\PoaFormatterPlatesExtensionFactory::class,
                View\Url\UrlHelperPlatesExtension::class => View\Url\UrlHelperPlatesExtensionFactory::class,
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
