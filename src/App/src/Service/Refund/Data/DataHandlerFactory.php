<?php
namespace App\Service\Refund\Data;

use Interop\Container\ContainerInterface;

use PDO;
use Zend\Crypt\PublicKey\Rsa;

class DataHandlerFactory
{

    public function __invoke(ContainerInterface $container)
    {

        //-------------------------------------
        // Database

        $config = $container->get('config');

        if (!isset($config['db']['postgresql'])) {
            throw new \UnexpectedValueException('PostgreSQL is not configured');
        }

        $dbconf = $config['db']['postgresql'];

        $dsn = "{$dbconf['adapter']}:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['dbname']}";

        $db = new PDO($dsn, $dbconf['username'], $dbconf['password'], $dbconf['options']);

        // Set PDO to throw exceptions on error
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        //-------------------------------------
        // Encryption - Account Details

        if (!isset($config['security']['rsa']['keys']['public']['bank'])) {
            throw new \UnexpectedValueException('Bank RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['public']['bank'];

        $rsaAccount = Rsa::factory([
            'public_key'    => $keyPath,
            'binary_output' => false,   // Thus base64
        ]);


        //-------------------------------------
        // Encryption - Everything

        if (!isset($config['security']['rsa']['keys']['public']['full'])) {
            throw new \UnexpectedValueException('Bank RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['public']['full'];

        $rsaEverything = Rsa::factory([
            'public_key'    => $keyPath,
            'binary_output' => false,   // Thus base64
        ]);


        //-------------------------------------
        // Salt Hash

        if (!isset($config['security']['hash']['salt'])) {
            throw new \UnexpectedValueException('Hash Salt is not configured');
        }

        $salt = $config['security']['hash']['salt'];

        if (strlen($salt) < 32) {
            throw new \UnexpectedValueException('Hash Salt is too short');
        }

        return new DataHandlerLocal($db, $rsaAccount, $salt);
    }
}
