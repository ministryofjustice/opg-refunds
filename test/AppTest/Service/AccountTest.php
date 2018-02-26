<?php

namespace AppTest\Service;

use App\Service\Account as AccountService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Zend\Log\Logger;

class AccountTest extends MockeryTestCase
{
    /**
     * @var AccountService
     */
    private $service;

    /**
     * @var Logger|MockInterface
     */
    private $logger;

    protected function setUp()
    {
        $this->service = new AccountService(__DIR__.'/../../../assets', 'UnitTest');
        $this->logger = Mockery::mock(Logger::class);
        $this->logger->shouldReceive('info');
        $this->logger->shouldReceive('debug');
        $this->service->setLogger($this->logger);
    }

    public function testIsBuildingSocietyFalse()
    {
        $result = $this->service->isBuildingSociety('NotABuildingSociety');

        $this->assertFalse($result);
    }

    public function testIsBuildingSocietyTrue()
    {
        $buildingSocietyHash = $second = key(array_slice($this->service->getBuildingSocietyHashes(), 10, 1));

        $result = $this->service->isBuildingSociety($buildingSocietyHash);

        $this->assertTrue($result);
    }

    public function testGetBuildingSocietyNameNull()
    {
        $result = $this->service->getBuildingSocietyName('NotABuildingSociety');

        $this->assertNull($result);
    }

    public function testGetBuildingSocietyName()
    {
        $buildingSocietyHash = $second = key(array_slice($this->service->getBuildingSocietyHashes(), 20, 1));

        $result = $this->service->getBuildingSocietyName($buildingSocietyHash);

        $this->assertEquals('Dunfermline Building Society', $result);
    }
}