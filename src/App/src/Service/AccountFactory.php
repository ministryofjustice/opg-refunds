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
        $config = $container->get('config');

        $sourceFolder = $config['spreadsheet']['source_folder'];
        $salt = $config['security']['hash']['salt'];

        return new Account($sourceFolder, $salt);
    }
}