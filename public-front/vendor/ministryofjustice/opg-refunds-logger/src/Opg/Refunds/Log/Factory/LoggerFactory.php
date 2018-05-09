<?php
namespace Opg\Refunds\Log\Factory;

use Opg\Refunds\Log\Logger;
use Opg\Refunds\Log\Formatter\Logstash;
use Opg\Refunds\Log\Writer\Sns as SnsWriter;

use Zend\Log\Writer\Stream as StreamWriter;

use Interop\Container\ContainerInterface;

use Aws\Sns\SnsClient;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {

        $config = $container->get('config');

        if (!isset($config['log'])) {
            throw new \UnexpectedValueException('Log config not found');
        }

        $config = $config['log'];

        //---

        $logger = new Logger;

        //---------------------------------------
        // Setup Logstash Logging

        if (!isset($config['logstash']['path'])) {
            throw new \UnexpectedValueException('Logstash path not configured');
        }

        if (!is_writable($config['logstash']['path'])) {
            throw new \UnexpectedValueException('Logstash path not configured');
        }

        $streamWriter = new StreamWriter($config['logstash']['path']);

        $streamWriter->setFormatter( new Logstash );

        $logger->addWriter($streamWriter);


        //---------------------------------------
        // Setup AWS SNS Alerting

        if (!isset($config['sns']['client'])) {
            throw new \UnexpectedValueException('AWS SNS client is not configured');
        }

        if (!isset($config['sns']['endpoints']) || !is_array($config['sns']['endpoints'])) {
            throw new \UnexpectedValueException('AWS SNS endpoints not set');
        }

        foreach ($config['sns']['endpoints'] as $key => $endpoint) {
            if (!isset($endpoint['arn'])) {
                throw new \UnexpectedValueException('AWS SNS ARN is null for key: '.$key);
            }
        }

        //---

        $snsClient = new SnsClient($config['sns']['client']);

        $sns = new SnsWriter($snsClient, $config['sns']['endpoints']);

        $logger->addWriter($sns);

        //---

        return $logger;
    }
}
