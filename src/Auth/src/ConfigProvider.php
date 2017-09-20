<?php

namespace Auth;

/**
 * The configuration provider for the Auth module
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
                Action\AuthAction::class => Action\AuthActionFactory::class,

                // Middleware
                Middleware\AuthMiddleware::class => Middleware\AuthMiddlewareFactory::class,

                //  Services
                Service\Authentication::class => Service\AuthenticationFactory::class,
            ],
        ];
    }
}
