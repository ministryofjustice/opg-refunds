<?php
namespace App\Service\Refund\Data;

use Interop\Container\ContainerInterface;

use PDO;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\BlockCipher;
use App\Crypt\Hybrid as HybridCipher;

class DataHandlerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        //-------------------------------------
        // Database

        if (!isset($config['db']['postgresql'])) {
            throw new \UnexpectedValueException('PostgreSQL is not configured');
        }

        $dbconf = $config['db']['postgresql'];

        $dsn = "{$dbconf['adapter']}:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['dbname']}";

        $db = new PDO($dsn, $dbconf['username'], $dbconf['password'], $dbconf['options']);

        // Set PDO to throw exceptions on error
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //---

        return new DataHandlerLocal($db);
    }
}
