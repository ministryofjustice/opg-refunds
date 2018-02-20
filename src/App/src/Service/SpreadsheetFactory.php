<?php

namespace App\Service;

use App\Service\Account as AccountService;
use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;
use Aws\Kms\KmsClient;

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
        $config = $container->get('config');

        return new Spreadsheet(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(KmsClient::class),
            $container->get(Rsa::class),
            $container->get(ClaimService::class),
            $container->get(AccountService::class),
            $config['spreadsheet']
        );
    }
}