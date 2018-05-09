<?php
namespace OpgTest\Refunds\Log\Formatter;

use Opg\Refunds\Log\Formatter\Logstash;

use PHPUnit\Framework\TestCase;

class LogstashTest extends TestCase
{

    private $exampleEvent = null;

    protected function setUp()
    {
        $this->exampleEvent = [
            'timestamp' => new \DateTime,
            'priority'  => 5,
            'priorityName' => 'NOTICE',
            'message'   => 'The message being logged',
            'extra' => [
                'bool' => true,
                'int' => 7,
                'string' => 'testing',
                'array' => [
                    'test' => true
                ]
            ]
        ];
    }

    public function testCanInstantiate()
    {
        $logstash = new Logstash();
        $this->assertInstanceOf(Logstash::class, $logstash);
    }

    public function testWithData()
    {
        $logstash = new Logstash();

        $result = $logstash->format($this->exampleEvent);

        $this->assertInternalType('string', $result);

        $result = json_decode($result, true);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('@version', $result);
        $this->assertArrayHasKey('@timestamp', $result);
        $this->assertArrayHasKey('priority', $result);
        $this->assertArrayHasKey('priorityName', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('extra', $result);
    }
}
