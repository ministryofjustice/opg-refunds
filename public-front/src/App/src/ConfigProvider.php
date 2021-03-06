<?php

namespace App;

use Alphagov;

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
                // Actions
                Action\WhoAction::class => Action\WhoAction::class,
                Action\ContactDetailsAction::class => Action\ContactDetailsAction::class,
                Action\ExecutorDetailsAction::class => Action\ExecutorDetailsAction::class,
                Action\DonorDetailsAction::class => Action\DonorDetailsAction::class,
                Action\AttorneyDetailsAction::class => Action\AttorneyDetailsAction::class,
                Action\WhenFeesPaidAction::class => Action\WhenFeesPaidAction::class,
                Action\DoneAction::class => Action\DoneAction::class,
                Action\DonorDeceasedAction::class => Action\DonorDeceasedAction::class,
                Action\SessionFinishedAction::class => Action\SessionFinishedAction::class,
                Action\CaseNumberAction::class => Action\CaseNumberAction::class,
                Action\PostcodeAction::class => Action\PostcodeAction::class,
                Action\TermsPageAction::class => Action\TermsPageAction::class,
                Action\AccessibilityAction::class => Action\AccessibilityAction::class,
                Action\PrivacyPageAction::class => Action\PrivacyPageAction::class,
                Action\CookiesPageAction::class => Action\CookiesPageAction::class,
                Action\ExceptionAction::class => Action\ExceptionAction::class,
                Action\ContactUsAction::class => Action\ContactUsAction::class,
                Action\CookiesCheckAction::class => Action\CookiesCheckAction::class,
                Action\StartRedirectAction::class => Action\StartRedirectAction::class,
                Action\ContactDetailsAssistedDigitalAction::class => Action\ContactDetailsAssistedDigitalAction::class,

                // Middleware
                Middleware\CacheControlMiddleware::class =>  Middleware\CacheControlMiddleware::class,
            ],
            'factories'  => [
                // 3rd Party
                Alphagov\Notifications\Client::class => Service\Notify\NotifyClientFactory::class,

                // Actions
                Action\AccountDetailsAction::class => Action\Factory\AccountDetailsFactory::class,
                Action\HealthCheckAction::class => Action\Factory\HealthCheckFactory::class,
                Action\SummaryAction::class => Action\Factory\SummaryFactory::class,
                Action\AssistedDigitalAction::class => Action\Factory\AssistedDigitalFactory::class,
                Action\HomePageAction::class => Action\Factory\HomePageFactory::class,

                // Middleware
                Middleware\Session\SessionMiddleware::class => Middleware\Session\SessionMiddlewareFactory::class,
                Middleware\ProcessingTime\ProcessingTimeMiddleware::class => Middleware\ProcessingTime\ProcessingTimeMiddlewareFactory::class,
                Middleware\AssistedDigital\AssistedDigitalMiddleware::class => Middleware\AssistedDigital\AssistedDigitalMiddlewareFactory::class,
                Middleware\Tracking\TrackingMiddleware::class => Middleware\Tracking\TrackingMiddlewareFactory::class,

                // Services
                Service\Session\SessionManager::class => Service\Session\SessionManagerFactory::class,
                Service\Refund\ProcessApplication::class => Service\Refund\ProcessApplicationFactory::class,
                Service\Refund\Data\DataHandlerInterface::class => Service\Refund\Data\DataHandlerFactory::class,
                Service\Refund\Data\BankDetailsHandler::class => Service\Refund\Data\BankDetailsHandlerFactory::class,
                Service\Refund\AssistedDigital\LinkToken::class => Service\Refund\AssistedDigital\LinkTokenFactory::class,
                Service\ErrorMapper\ErrorMapper::class => Service\ErrorMapper\ErrorMapperFactory::class,

                // View Helper
                Service\AssetsCache\AssetsCachePlatesExtension::class => Service\AssetsCache\AssetsCachePlatesExtensionFactory::class,
                Service\ErrorMapper\ErrorMapperPlatesExtension::class => Service\ErrorMapper\ErrorMapperPlatesExtensionFactory::class,
                Service\TemplateConfigInjection\TemplateConfigInjectionPlatesExtension::class => Service\TemplateConfigInjection\TemplateConfigInjectionPlatesExtensionFactory::class,
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
