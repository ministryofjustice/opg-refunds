<?php

namespace Auth\Service;

use Interop\Container\ContainerInterface;

/**
 * Factory class to inject dependencies into the authentication service
 *
 * Class AuthenticationServiceFactory
 * @package Auth\Middleware
 */
class AuthenticationServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        return new AuthenticationService(
            $container->get('doctrine.entity_manager.orm_cases'),
            $config['token_ttl']
        );
    }
}
