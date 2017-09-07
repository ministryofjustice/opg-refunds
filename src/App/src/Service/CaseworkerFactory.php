<?php

namespace App\Service;

use Interop\Container\ContainerInterface;

/**
 * Class CaseworkerFactory
 * @package App\Service
 */
class CaseworkerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Caseworker
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Caseworker(
            $container->get('doctrine.entity_manager.orm_cases')
        );
    }
}
