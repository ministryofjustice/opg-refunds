<?php

namespace Auth\Service;

use Interop\Container\ContainerInterface;

/**
 * Factory class to inject dependencies into the authentication service
 *
 * Class AuthenticationFactory
 * @package Auth\Middleware
 */
class AuthenticationFactory
{
    /**
     * @param ContainerInterface $container
     * @return Authentication
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        return new Authentication(
            $container->get('doctrine.entity_manager.orm_cases'),
            $config['token_ttl']
        );
    }
}
