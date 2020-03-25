<?php

namespace App\Service\Session;

use Aws\DynamoDb\DynamoDbClient;
use Interop\Container\ContainerInterface;
use Laminas\Crypt\BlockCipher;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;
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

        //  Set up the connection to dynamoDB and the block cipher
        $dynamoDbConfig = $config['dynamodb'];

        $dynamoDbClient = new DynamoDbClient($dynamoDbConfig['client']);
        $connection = new SaveHandler\HashedKeyDynamoDbSessionConnection($dynamoDbClient, $dynamoDbConfig['settings']);

        //  Create the save handler for the session manager
        $saveHandler = new SaveHandler\DynamoDB($connection);

        //  Set up the session manager and return it
        $manager = new SessionManager();
        $manager->setSaveHandler($saveHandler);

        $manager->start();

        //  Set the session manager in the container so it is always used
        Container::setDefaultManager($manager);

        return $manager;
    }
}
