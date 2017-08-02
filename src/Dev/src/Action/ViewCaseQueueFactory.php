<?php
namespace Dev\Action;

use PDO;
use Zend\Crypt\PublicKey\Rsa;
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
        // Encryption

        if (!isset($config['security']['rsa']['key']['private'])) {
            throw new \UnexpectedValueException('RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['key']['private'];

        $rsa = Rsa::factory([
            'private_key'    => $keyPath,
        ]);

        //---

        return new ViewCaseQueueAction($db, $rsa);
    }
}
