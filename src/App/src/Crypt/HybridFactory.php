<?php

namespace App\Crypt;

use Interop\Container\ContainerInterface;
use Zend\Crypt\PublicKey\Rsa;
use Zend\Crypt\BlockCipher;

class HybridFactory
{
    /**
     * @param ContainerInterface $container
     * @return Hybrid
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['security']['rsa']['keys']['private']['full'])) {
            throw new \UnexpectedValueException('RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['private']['full'];

        $hybrid = new Hybrid(
            BlockCipher::factory('openssl', ['algo' => 'aes']),
            Rsa::factory(['private_key'=> $keyPath])
        );

        return $hybrid;
    }
}