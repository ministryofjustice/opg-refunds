<?php
namespace App\Service\Refund\Beta;

use Interop\Container\ContainerInterface;
use Aws\DynamoDb\DynamoDbClient;

class BetaLinkCheckerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['beta'])) {
            throw new \UnexpectedValueException('Beta not configured');
        }

        $config = $config['beta'];

        //---

        if (!isset($config['enabled'])) {
            throw new \UnexpectedValueException('Beta enabled not configured');
        }

        //---

        if (
            !isset($config['link']['signature']['key']) ||
            mb_strlen(hex2bin($config['link']['signature']['key']), '8bit') != 32
        ){
            throw new \UnexpectedValueException('Signature key not configured');
        }

        //---

        if (!isset($config['dynamodb']['client']) || !isset($config['dynamodb']['settings'])) {
            throw new \UnexpectedValueException('Dynamo DB for sessions not configured');
        }

        $dynamoDbClient = new DynamoDbClient($config['dynamodb']['client']);

        return new BetaLinkChecker(
            $dynamoDbClient,
            $config['dynamodb']['settings'],
            $config['link']['signature']['key'],
            $config['enabled']
        );
    }
}
