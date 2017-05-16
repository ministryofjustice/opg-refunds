<?php
namespace App\Service\Session;

use Interop\Container\ContainerInterface;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\StandardSessionConnection;

use Zend\Crypt\BlockCipher;

class SessionManagerFactory
{

    public function __invoke(ContainerInterface $container)
    {

        $config =  $container->get( 'config' )['session'];

        // Copy TTL value into DynamoDb session_lifetime
        $config['dynamodb']['settings']['session_lifetime'] = $config['ttl'];

        //---

        $dynamoDbClient = new DynamoDbClient( $config['dynamodb']['client'] );

        $sessionConnection = new StandardSessionConnection( $dynamoDbClient, $config['dynamodb']['settings'] );

        //---

        $blockCipher = BlockCipher::factory('openssl', array('algo' => 'aes'));

        $blockCipher->setKey( $config['encryption']['key'] );

        //---

        return new SessionManager( $sessionConnection, $blockCipher );
    }

}