<?php

namespace App\Service\Auth;

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
        // Retrieve any dependencies from the container when creating the instance
        return new AuthAdapter(/* any dependencies */);
    }
}
