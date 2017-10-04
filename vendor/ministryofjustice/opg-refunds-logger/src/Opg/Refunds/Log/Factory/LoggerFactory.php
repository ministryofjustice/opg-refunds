<?php
namespace Opg\Refunds\Log\Factory;

use Opg\Refunds\Log\Logger;
use Opg\Refunds\Log\Formatter\Logstash;

use Zend\Log\Writer\Stream as StreamWriter;

use Interop\Container\ContainerInterface;

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

        //---

        return $logger;
    }
}
