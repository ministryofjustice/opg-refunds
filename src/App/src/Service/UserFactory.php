<?php

namespace App\Service;

use Interop\Container\ContainerInterface;

/**
 * Class UserFactory
 * @package App\Service
 */
class UserFactory
{
    /**
     * @param ContainerInterface $container
     * @return User
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        return new User(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(TokenGenerator::class),
            $config['password_reset_ttl']
        );
    }
}
