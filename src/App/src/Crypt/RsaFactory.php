<?php

namespace App\Crypt;

use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;

class RsaFactory
{
    /**
     * @param ContainerInterface $container
     * @return Rsa
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['security']['rsa']['keys']['private']['bank'])) {
            throw new \UnexpectedValueException('RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['private']['bank'];

        $rsa = Rsa::factory([
            'private_key' => $keyPath,
        ]);

        return $rsa;
    }
}