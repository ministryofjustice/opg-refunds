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
                Action\LoginAction::class => Action\LoginAction::class,
                Action\SignoutAction::class => Action\SignoutAction::class,
                Action\PasswordSetNewAction::class => Action\PasswordSetNewAction::class,
                Action\PasswordRequestResetAction::class => Action\PasswordRequestResetAction::class,
            ],
            'factories'  => [
                // Middleware
                Middleware\Session\SessionMiddleware::class => Middleware\Session\SessionMiddlewareFactory::class,

                // Services
                Service\Session\SessionManager::class => Service\Session\SessionManagerFactory::class,
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
            ],
        ];
    }
}
