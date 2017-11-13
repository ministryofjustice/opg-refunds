<?php
namespace App\Crypt;

use Aws\Kms\KmsClient;
use Interop\Container\ContainerInterface;

class AwsKmsFactory
{
    /**
     * @param ContainerInterface $container
     * @return KmsClient
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['security']['kms'])) {
            throw new \UnexpectedValueException('AWS KMS is not configured');
        }

        $kmsConfig = $config['security']['kms'];

        if (!isset($kmsConfig['client'])) {
            throw new \UnexpectedValueException('AWS KMS Client is not configured');
        }

        return new KmsClient($kmsConfig['client']);
    }
}
