<?php

namespace App\Service\Session;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\StandardSessionConnection;
use Interop\Container\ContainerInterface;
use Zend\Crypt\BlockCipher;

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

        if (!isset($config['session']['ttl'])) {
            throw new \UnexpectedValueException('Session TTL not configured');
        }

        $config = $config['session'];

        // Copy TTL value into DynamoDb session_lifetime
        $config['dynamodb']['settings']['session_lifetime'] = $config['ttl'];

        //---

        if (!isset($config['dynamodb']['client']) || !isset($config['dynamodb']['settings'])) {
            throw new \UnexpectedValueException('Dynamo DB for sessions not configured');
        }

        $dynamoDbClient = new DynamoDbClient($config['dynamodb']['client']);

        $sessionConnection = new StandardSessionConnection($dynamoDbClient, $config['dynamodb']['settings']);

        //---

        if (!isset($config['encryption']['keys'])) {
            throw new \UnexpectedValueException('Session encryption keys not configured');
        }

        $keys = explode(',', $config['encryption']['keys']);

        $keyChain = new KeyChain;

        foreach ($keys as $key) {
            $items = explode(':', $key);

            $value = hex2bin($items[1]);
            if (count($items) != 2 || mb_strlen($value, '8bit') < 32) {
                throw new \UnexpectedValueException('Session encryption key is too short');
            }

            $keyChain->offsetSet($items[0], $value);
        }

        $keyChain->ksort();

        //---

        $blockCipher = BlockCipher::factory('openssl', ['algo' => 'aes']);

        //---

        return new SessionManager($sessionConnection, $blockCipher, $keyChain);
    }
}
