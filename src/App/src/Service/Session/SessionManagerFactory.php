<?php

namespace App\Service\Session;

use Aws\DynamoDb\DynamoDbClient;
use Interop\Container\ContainerInterface;
use Zend\Crypt\BlockCipher;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use UnexpectedValueException;

/**
 * Class SessionManagerFactory
 * @package App\Service\Session
 */
class SessionManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return SessionManager
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['session'])) {
            throw new UnexpectedValueException('Session configuration not found');
        }

        $config = $config['session'];

        //  Apply any native PHP level settings
        if (isset($config['native_settings']) && is_array($config['native_settings'])) {
            foreach ($config['native_settings'] as $k => $v) {
                ini_set('session.' . $k, $v);
            }
        }

        //  Set up the required keys
        if (!isset($config['encryption']['keys'])) {
            throw new UnexpectedValueException('Session encryption keys not configured');
        }

        //  Build the key chain
        $keyChain = new KeyChain(explode(',', $config['encryption']['keys']));

        //  TODO - This ksort is done the in EncryptedDynamoDB class too - perhaps it doesn't need to be done here
        $keyChain->ksort();
        $keys = $keyChain->getArrayCopy();

        //  Set up the connection to dynamoDB and the block cipher
        $dynamoDbConfig = $config['dynamodb'];

        $dynamoDbClient = new DynamoDbClient($dynamoDbConfig['client']);
        $connection = new SaveHandler\HashedKeyDynamoDbSessionConnection($dynamoDbClient, $dynamoDbConfig['settings']);

        $blockCipher = BlockCipher::factory('openssl', [
            'algo' => 'aes'
        ]);

        //  Create the save handler for the session manager
        $saveHandler = new SaveHandler\EncryptedDynamoDB($connection);
        $saveHandler->setBlockCipher($blockCipher, $keys);

        //  Set up the session manager and return it
        $manager = new SessionManager();
        $manager->setSaveHandler($saveHandler);

        $manager->start();

        //  Set the session manager in the container so it is always used
        Container::setDefaultManager($manager);

        return $manager;
    }
}
