<?php
namespace AppTest\Service\Refund;

use PHPUnit\Framework\TestCase;

use App\Service\Refund\ProcessApplication;

use Laminas\Log\Logger;
use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Refund\Data\DataHandlerInterface;

use Prophecy\Argument;

class ProcessApplicationTest extends TestCase
{
    const JSON_SCHEMA_FILE = '/tmp/unit-test-schema.json';

    const TEST_CLAIM_CODE = '12345678912';

    private $notifyClient;
    private $dataHandler;
    private $logger;

    public static function setUpBeforeClass()
    {
        file_put_contents(self::JSON_SCHEMA_FILE, '{"$schema": "http://json-schema.org/draft-04/schema#"}');
    }

    public static function tearDownAfterClass()
    {
        unlink(self::JSON_SCHEMA_FILE);
    }

    //-----

    protected function setUp()
    {
        $this->logger = $this->prophesize(Logger::class);
        $this->dataHandler = $this->prophesize(DataHandlerInterface::class);
        $this->notifyClient = $this->prophesize(NotifyClient::class);

        $this->dataHandler->store( Argument::type('array') )->willReturn(self::TEST_CLAIM_CODE);
    }

    protected function getInstance()
    {
        $processor = new ProcessApplication(
            $this->notifyClient->reveal(),
            $this->dataHandler->reveal(),
            self::JSON_SCHEMA_FILE
        );

        $processor->setLogger($this->logger->reveal());

        return $processor;
    }

    protected function getTestData()
    {
        return [
            'donor' => [
                'current' => [
                    'name' => [
                        'first' => 'Fred',
                        'last' => 'Sanders',
                    ],
                    'dob' => '1978-10-23'
                ],
            ],
            'contact' => [
                'receive-notifications' => true
            ],
            'expected' => '12 weeks',
        ];
    }

    //--------------------------------------

    public function testCanInstantiate()
    {
        $processor = $this->getInstance();
        $this->assertInstanceOf(ProcessApplication::class, $processor);
    }


    public function testWithEmptyContactDetails()
    {
        $processor = $this->getInstance();

        $result = $processor->process($this->getTestData());

        $this->assertInternalType('string', $result);
        $this->assertEquals(self::TEST_CLAIM_CODE, $result);
    }

    //---------------------------------------------
    // Test the 5 combinations of contact details

    public function testWithEmailAddress()
    {
        $processor = $this->getInstance();

        $data = $this->getTestData();

        $data['contact']['email'] = 'test@example.com';

        $this->notifyClient->sendEmail(
            $data['contact']['email'],
            Argument::type('string'),
            Argument::type('array'))->shouldBeCalled();

        $this->notifyClient->sendSms(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $processor->process($data);
    }

    public function testWithMobileNumber()
    {
        $processor = $this->getInstance();

        $data = $this->getTestData();

        $data['contact']['phone'] = '07811111111';

        $this->notifyClient->sendEmail(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $this->notifyClient->sendSms(
            $data['contact']['phone'],
            Argument::type('string'),
            Argument::type('array'))->shouldBeCalled();

        $processor->process($data);
    }

    public function testWithMobileAndEmail()
    {
        $processor = $this->getInstance();

        $data = $this->getTestData();

        $data['contact']['phone'] = '07811111111';
        $data['contact']['email'] = 'test@example.com';

        $this->notifyClient->sendEmail(
            $data['contact']['email'],
            Argument::type('string'),
            Argument::type('array'))->shouldBeCalled();

        $this->notifyClient->sendSms(
            $data['contact']['phone'],
            Argument::type('string'),
            Argument::type('array'))->shouldBeCalled();

        $processor->process($data);
    }

    public function testWithLandlineAndEmail()
    {
        $processor = $this->getInstance();

        $data = $this->getTestData();

        $data['contact']['phone'] = '02012345678';
        $data['contact']['email'] = 'test@example.com';

        $this->notifyClient->sendEmail(
            $data['contact']['email'],
            Argument::type('string'),
            Argument::type('array'))->shouldBeCalled();

        $this->notifyClient->sendSms(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $processor->process($data);
    }

    public function testWithLandline()
    {
        $processor = $this->getInstance();

        $data = $this->getTestData();

        $data['contact']['phone'] = '02012345678';

        $this->notifyClient->sendEmail(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $this->notifyClient->sendSms(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $processor->process($data);
    }

    public function testWithMobileAndEmailButNoNotifications()
    {
        $processor = $this->getInstance();

        $data = $this->getTestData();

        $data['contact']['receive-notifications'] = false;

        $data['contact']['phone'] = '07811111111';
        $data['contact']['email'] = 'test@example.com';

        $this->notifyClient->sendEmail(
            $data['contact']['email'],
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $this->notifyClient->sendSms(
            $data['contact']['phone'],
            Argument::type('string'),
            Argument::type('array'))->shouldNotBeCalled();

        $processor->process($data);
    }

}