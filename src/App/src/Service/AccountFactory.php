<?php

namespace App\Service;

use Interop\Container\ContainerInterface;

class AccountFactory
{
    /**
     * @var Account
     */
    private static $instance;

    /**
     * @param ContainerInterface $container
     * @return Account
     */
    public function __invoke(ContainerInterface $container)
    {
        if (is_null(self::$instance)) {
            $config = $container->get('config');

            $sourceFolder = $config['spreadsheet']['source_folder'];
            $salt = $config['security']['hash']['salt'];

            self::$instance = new Account($sourceFolder, $salt);
        }

        return self::$instance;
    }
}