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
                //  Services
                Service\TokenGenerator::class => Service\TokenGenerator::class,
            ],
            'factories'  => [
                //  Actions
                Action\AuthAction::class => Action\AuthActionFactory::class,

                // Middleware
                Middleware\AuthorizationMiddleware::class => Middleware\AuthorizationMiddlewareFactory::class,

                //  Services
                Service\Authentication::class => Service\AuthenticationFactory::class,
            ],
        ];
    }
}
