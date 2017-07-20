<?php

namespace App;

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
                Action\HomePageAction::class => Action\HomePageAction::class,
                Action\PingAction::class => Action\PingAction::class,
                Action\TestAction::class => Action\TestAction::class,
                Action\SummaryAction::class => Action\SummaryAction::class,
                Action\WhoAction::class => Action\WhoAction::class,
                Action\ContactDetailsAction::class => Action\ContactDetailsAction::class,
                Action\DonorDetailsAction::class => Action\DonorDetailsAction::class,
                Action\AttorneyDetailsAction::class => Action\AttorneyDetailsAction::class,
                Action\WhenFeesPaidAction::class => Action\WhenFeesPaidAction::class,
                Action\DoneAction::class => Action\DoneAction::class,
                Action\DonorDeceasedAction::class => Action\DonorDeceasedAction::class,
                Action\VerificationDetailsAction::class => Action\VerificationDetailsAction::class,
                Action\SessionFinishedAction::class => Action\SessionFinishedAction::class,

                // Middleware
                Middleware\CacheControlMiddleware::class =>  Middleware\CacheControlMiddleware::class,
            ],
            'factories'  => [
                // 3rd Party
                \Alphagov\Notifications\Client::class => Service\Notify\NotifyClientFactory::class,

                // Actions
                Action\AccountDetailsAction::class => Action\AccountDetailsFactory::class,

                // Middleware
                Middleware\Session\SessionMiddleware::class => Middleware\Session\SessionMiddlewareFactory::class,

                // Services
                Service\Session\SessionManager::class => Service\Session\SessionManagerFactory::class,
                Service\Refund\ProcessApplication::class => Service\Refund\ProcessApplicationFactory::class,
                Service\Refund\Data\DataHandlerInterface::class => Service\Refund\Data\DataHandlerFactory::class
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
