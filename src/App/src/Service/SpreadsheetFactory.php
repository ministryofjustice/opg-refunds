<?php

namespace App\Service;

use App\Service\Account as AccountService;
use App\Service\Claim as ClaimService;
use Interop\Container\ContainerInterface;
use PDO;
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

        //-------------------------------------
        // Database

        if (!isset($config['db']['cases']['postgresql'])) {
            throw new \UnexpectedValueException('PostgreSQL is not configured');
        }

        $dbConf = $config['db']['cases']['postgresql'];

        $dsn = "{$dbConf['adapter']}:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbConf['dbname']}";

        $db = new PDO($dsn, $dbConf['username'], $dbConf['password'], $dbConf['options']);

        // Set PDO to throw exceptions on error
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return new Spreadsheet(
            $container->get('doctrine.entity_manager.orm_cases'),
            $container->get(KmsClient::class),
            $container->get(Rsa::class),
            $container->get(ClaimService::class),
            $container->get(AccountService::class),
            $config['spreadsheet'],
            $db
        );
    }
}