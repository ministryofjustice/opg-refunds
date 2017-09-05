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

        //  TODO - Inject DB access here also

        return new AuthenticationService($config['token_ttl']);
    }
}
