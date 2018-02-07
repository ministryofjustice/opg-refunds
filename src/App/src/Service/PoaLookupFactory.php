<?php

namespace App\Service;

use PDO;
use Interop\Container\ContainerInterface;

class PoaLookupFactory {

    /**
     * @param ContainerInterface $container
     * @return PoaLookup
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        //-------------------------------------
        // Database

        if (!isset($config['db']['cases']['postgresql'])) {
            throw new \UnexpectedValueException('PostgreSQL is not configured');
        }

        $dbconf = $config['db']['cases']['postgresql'];

        $dsn = "{$dbconf['adapter']}:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['dbname']}";

        $db = new PDO($dsn, $dbconf['username'], $dbconf['password'], $dbconf['options']);

        // Set PDO to throw exceptions on error
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return new PoaLookup($db);
    }

}
