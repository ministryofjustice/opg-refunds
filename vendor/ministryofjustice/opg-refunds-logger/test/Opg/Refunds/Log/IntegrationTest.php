<?php
namespace OpgTest\Refunds\Log;

use Opg\Refunds\Log\Logger;
use Opg\Refunds\Log\Formatter\Logstash;

use Zend\Log\Writer\Mock as MockWriter;

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{

    public function testLoggerWithLogstashFormatter()
    {
        $file = '/tmp/refunds-log-unit-test-'.time();

        $writer = new \Zend\Log\Writer\Stream($file);

        $logstashFormatter = new Logstash();
        $writer->setFormatter($logstashFormatter);

        $logger = new Logger();
        $logger->addWriter($writer);

        //---

        $logger->err('Message to log');

        //---

        $result = json_decode(file_get_contents($file), true);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('@version', $result);
        $this->assertArrayHasKey('@timestamp', $result);
        $this->assertArrayHasKey('priority', $result);
        $this->assertArrayHasKey('priorityName', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('extra', $result);

        //---

        unlink($file);

    }

}