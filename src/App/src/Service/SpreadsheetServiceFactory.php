<?php

namespace App\Service;

use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

/**
 * Class SpreadsheetServiceFactory
 * @package App\Service
 */
class SpreadsheetServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return SpreadsheetService
     */
    public function __invoke(ContainerInterface $container)
    {
        return new SpreadsheetService(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(Rsa::class)
        );
    }
}