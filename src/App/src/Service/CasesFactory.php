<?php

namespace App\Service;

use Interop\Container\ContainerInterface;

/**
 * Class CasesFactory
 * @package App\Service
 */
class CasesFactory
{
    /**
     * @param ContainerInterface $container
     * @return Cases
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Cases(
            $container->get('doctrine.entity_manager.orm_cases')
        );
    }
}
