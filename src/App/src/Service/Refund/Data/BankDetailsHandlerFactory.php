<?php
namespace App\Service\Refund\Data;

use Zend\Crypt\PublicKey\Rsa;
use Interop\Container\ContainerInterface;
use Aws\Kms\KmsClient;

class BankDetailsHandlerFactory
{

    public function __invoke(ContainerInterface $container)
    {

        $config = $container->get('config');


        //-------------------------------------
        // KMS Setup

        if (!isset($config['security']['kms'])) {
            throw new \UnexpectedValueException('AWS KMS is not configured');
        }

        $kmsConfig = $config['security']['kms'];

        if (!isset($kmsConfig['client'])) {
            throw new \UnexpectedValueException('AWS KMS Client is not configured');
        }

        $kmsClient = new KmsClient($kmsConfig['client']);

        //---

        if (!isset($kmsConfig['settings']['keyId'])) {
            throw new \UnexpectedValueException('AWS KMS KeyId is not configured');
        }

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

        return new BankDetailsHandler(
            $kmsClient,
            $kmsConfig['settings']['keyId'],
            $salt
        );
    }
}
