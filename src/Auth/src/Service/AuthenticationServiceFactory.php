<?php

namespace Auth\Service;

use App\Service\Caseworker as CaseworkerService;
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
            $container->get(CaseworkerService::class),
            $config['token_ttl']
        );
    }
}
