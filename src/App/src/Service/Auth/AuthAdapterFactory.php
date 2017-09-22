<?php

namespace App\Service\Auth;

use Api\Service\Client as ApiClient;
use Interop\Container\ContainerInterface;

/**
 * Class AuthAdapterFactory
 * @package App\Service\Auth
 */
class AuthAdapterFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthAdapter(
            $container->get(ApiClient::class)
        );
    }
}
