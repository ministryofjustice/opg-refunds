<?php
namespace Dev\Action;

use PDO;
use App\Crypt\Hybrid as HybridCipher;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\BlockCipher;
use Interop\Container\ContainerInterface;

class ViewCaseQueueFactory
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
        // Encryption - Everything

        if (!isset($config['security']['rsa']['keys']['private']['full'])) {
            throw new \UnexpectedValueException('RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['private']['full'];

        $hybrid = new HybridCipher(
            BlockCipher::factory('openssl', ['algo' => 'aes']),
            Rsa::factory(['private_key'=> $keyPath])
        );

        //---

        return new ViewCaseQueueAction($db, $rsa, $hybrid);
    }
}
