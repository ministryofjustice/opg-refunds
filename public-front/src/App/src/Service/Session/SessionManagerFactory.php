<?php
namespace App\Service\Session;

use Interop\Container\ContainerInterface;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\StandardSessionConnection;

class SessionManagerFactory
{

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

        return new SessionManager($sessionConnection);
    }
}
