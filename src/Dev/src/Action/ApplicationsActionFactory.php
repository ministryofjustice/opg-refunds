<?php

namespace Dev\Action;

use PDO;
use Aws\Kms\KmsClient;
use App\Crypt\Hybrid as HybridCipher;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\BlockCipher;
use Interop\Container\ContainerInterface;

/**
 * Class ApplicationsActionFactory
 * @package Dev\Action
 */
class ApplicationsActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        //-------------------------------------
        // Database

        if (!isset($config['db']['incoming']['postgresql'])) {
            throw new \UnexpectedValueException('PostgreSQL is not configured');
        }

        $dbconf = $config['db']['incoming']['postgresql'];

        $dsn = "{$dbconf['adapter']}:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['dbname']}";

        $db = new PDO($dsn, $dbconf['username'], $dbconf['password'], $dbconf['options']);

        // Set PDO to throw exceptions on error
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        //-------------------------------------
        // Encryption - Bank

        if (!isset($config['security']['rsa']['keys']['private']['bank'])) {
            throw new \UnexpectedValueException('RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['private']['bank'];

        $rsa = Rsa::factory([
            'private_key' => $keyPath,
        ]);

        //-------------------------------------
        // KMS Setup

        if (!isset($config['security']['kms'])) {
            throw new \UnexpectedValueException('AWS KMS is not configured');
        }

        $kmsConfig = $config['security']['kms'];

        if (!isset($kmsConfig['client'])) {
            throw new \UnexpectedValueException('AWS KMS Client is not configured');
        }

        $kmsClient = new KmsClient($kmsConfig['client']);

        //---

        $casesEntityManager = $container->get('doctrine.entity_manager.orm_cases');

        return new ApplicationsAction($db, $casesEntityManager, $rsa, $kmsClient);
    }
}