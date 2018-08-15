<?php
namespace AppTest\Service\Refund;

use PHPUnit\Framework\TestCase;

use App\Service\Refund\ProcessApplication;

use Zend\Log\Logger;
use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Refund\Data\DataHandlerInterface;

use UnexpectedValueException;

use Prophecy\Argument;

class ProcessApplicationSchemaTest extends TestCase
{
    const TEST_CLAIM_CODE = '12345678912';

    private $notifyClient;
    private $dataHandler;
    private $logger;
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
        $schemaPath = dirname(__FILE__).'/../../../../config/json-schema.json';

        if (!is_readable($schemaPath)) {
            throw new \RuntimeException("Unable to local json-schema file");
        }

        //---

        $processor = new ProcessApplication(
            $this->notifyClient->reveal(),
            $this->dataHandler->reveal(),
            $schemaPath
        );

        $processor->setLogger($this->logger->reveal());

        return $processor;
    }

    protected function getTestData()
    {
        return [
            'applicant' => 'donor',
            'donor' => [
                'poa' => [
                    'name' => [
                        'title' => 'Mr',
                        'first' => 'Fred',
                        'last' => 'Sanders',
                    ],
                ],
                'current' => [
                    'name' => [
                        'title' => 'Mr',
                        'first' => 'Fred',
                        'last' => 'Sanders',
                    ],
                    'dob' => '1978-10-23'
                ],
            ],
            'attorney' => [
                'poa' => [
                    'name' => [
                        'title' => 'Miss',
                        'first' => 'Jane',
                        'last' => 'Smith',
                    ],
                ],
                'current' => [
                    'name' => [
                        'title' => 'Miss',
                        'first' => 'Jane',
                        'last' => 'Smith',
                    ],
                    'dob' => '1968-10-23'
                ],
            ],
            'contact' => [
                'receive-notifications' => true,
                'email' => 'test@example.com'
            ],
            'expected' => '2018-08-21',
        ];
    }

    //--------------------------------------

    public function testCanInstantiate()
    {
        $processor = $this->getInstance();
        $this->assertInstanceOf(ProcessApplication::class, $processor);
    }

    //--------------------------------------

    public function testCanPassWithValidData(){
        $processor = $this->getInstance();
        $data = $this->getTestData();

        $processor->process($data);

        // We're simply checking an exception is not thrown, thus if not, we can asset the test passed.
        $this->assertTrue(true);
    }

    //---

    public function testInvalidTextField(){
        $processor = $this->getInstance();
        $data = $this->getTestData();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '|/definitions/validation/stdTextField|' );

        $data['donor']['current']['name']['first'] = "Bob\nStanders";
        $processor->process($data);
    }

    //---

    public function testInvalidDateFormat(){
        $processor = $this->getInstance();
        $data = $this->getTestData();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '|/definitions/fieldset/dob|' );

        $data['donor']['current']['dob'] = '9 June 1973';
        $processor->process($data);
    }

    public function testInvalidNameFormat(){
        $processor = $this->getInstance();
        $data = $this->getTestData();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '|/definitions/fieldset/name|' );

        $data['donor']['current']['name'] = 'Mr Bob Sanders';
        $processor->process($data);
    }

    public function testInvalidNameRequiredField(){
        $processor = $this->getInstance();
        $data = $this->getTestData();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '|/definitions/fieldset/name|' );

        unset($data['donor']['current']['name']['first']);
        $processor->process($data);
    }

    public function testInvalidEmail(){
        $processor = $this->getInstance();
        $data = $this->getTestData();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '|/properties/email|' );

        $data['contact']['email'] = 'example.com';
        $processor->process($data);
    }
}