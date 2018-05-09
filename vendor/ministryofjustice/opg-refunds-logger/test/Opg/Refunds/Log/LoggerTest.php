<?php
namespace OpgTest\Refunds\Log;

use Opg\Refunds\Log\Logger;

use Zend\Log\Writer\Mock as MockWriter;

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{

    public function testCanInstantiate()
    {
        $logger = new Logger();
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testLogWithNoWriters()
    {
        $logger = new Logger();

        $this->expectException(\Zend\Log\Exception\RuntimeException::class);

        $logger->notice('Testing');
    }

    public function testLogWithWriter()
    {
        $logger = new Logger;
        $writer = new MockWriter;

        $logger->addWriter( $writer );

        //---

        $extra = [
            'bool' => true,
            'int' => 7,
            'string' => 'testing',
            'array' => [
                'test' => true
            ]
        ];

        $logger->notice('Testing', $extra);

        $data = $writer->events;

        $this->assertInternalType('array', $data);

        $data = array_pop($data);

        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('priority', $data);
        $this->assertArrayHasKey('priorityName', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('extra', $data);

        $this->assertEquals('Testing',  $data['message']);
        $this->assertEquals(5,          $data['priority']);
        $this->assertEquals('NOTICE',   $data['priorityName']);
        $this->assertEquals($extra,     $data['extra']);

        $this->assertInstanceOf('DateTime', $data['timestamp']);
    }
}
