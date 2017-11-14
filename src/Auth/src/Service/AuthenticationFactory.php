<?php

namespace Auth\Service;

use App\Service\TokenGenerator;
use App\Service\User as UserService;
use Interop\Container\ContainerInterface;

/**
 * Factory class to inject dependencies into the authentication service
 *
 * Class AuthenticationFactory
 * @package Auth\Service
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
            $container->get(UserService::class),
            $container->get(TokenGenerator::class),
            $config['token_ttl']
        );
    }
}
