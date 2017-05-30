<?php

namespace AppTest\Service\Notify;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;

use App\Service\Notify\NotifyClientFactory;
use Alphagov\Notifications\Client as NotifyClient;
use Http\Client\HttpClient;

class NotifyClientFactoryTest extends TestCase
{

    const UUID_EXAMPLE = '735d3c17-c81a-43dd-9950-61107c0a39a6';

    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

    }

    public function testFactoryWithoutApiKeyConfigured()
    {

        $factory = new NotifyClientFactory();

        $this->assertInstanceOf(NotifyClientFactory::class, $factory);

        $this->expectException(\UnexpectedValueException::class);

        $factory($this->container->reveal());

    }

    public function testFactoryWithoutHttpClientConfigured()
    {

        $this->container->get( 'config' )->willReturn( array(
            'notify' => [ 'api' => [ 'key' => self::UUID_EXAMPLE  ] ]
        ));

        $this->container->get(HttpClient::class)->willReturn(null);

        $factory = new NotifyClientFactory();

        $this->assertInstanceOf(NotifyClientFactory::class, $factory);

        $this->expectException(\Alphagov\Notifications\Exception\InvalidArgumentException::class);

        $factory($this->container->reveal());

    }

    public function testFactoryWithFullConfiguration()
    {

        $this->container->get( 'config' )->willReturn( array(
            'notify' => [ 'api' => [ 'key' => self::UUID_EXAMPLE   ] ]
        ));

        $httpClient = $this->prophesize(HttpClient::class);

        $this->container->get(HttpClient::class)->willReturn($httpClient);

        $factory = new NotifyClientFactory();

        $this->assertInstanceOf(NotifyClientFactory::class, $factory);

        $client = $factory($this->container->reveal());

        $this->assertInstanceOf(NotifyClient::class, $client);

    }

}