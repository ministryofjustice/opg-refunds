<?php

namespace App\Service;

use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

/**
 * Class SpreadsheetFactory
 * @package App\Service
 */
class SpreadsheetFactory
{
    /**
     * @param ContainerInterface $container
     * @return Spreadsheet
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Spreadsheet(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(Rsa::class),
            $container->get(ClaimService::class)
        );
    }
}