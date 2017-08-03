<?php
namespace App\Service\Refund\Data;

use Zend\Crypt\PublicKey\Rsa;
use Interop\Container\ContainerInterface;

class BankDetailsHandlerFactory
{

    public function __invoke(ContainerInterface $container)
    {

        $config = $container->get('config');

        //-------------------------------------
        // Encryption - Account Details

        if (!isset($config['security']['rsa']['keys']['public']['bank'])) {
            throw new \UnexpectedValueException('Bank RSA public key is not configured');
        }

        $keyPath = $config['security']['rsa']['keys']['public']['bank'];

        $cipher = Rsa::factory([
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

        //---

        return new BankDetailsHandler($cipher, $salt);
    }
}
