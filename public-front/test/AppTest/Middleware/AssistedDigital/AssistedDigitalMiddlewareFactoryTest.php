<?php
namespace AppTest\Middleware\AssistedDigital;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;

use App\Middleware\AssistedDigital\AssistedDigitalMiddleware;
use App\Middleware\AssistedDigital\AssistedDigitalMiddlewareFactory;

use App\Service\Refund\AssistedDigital\LinkToken;
use League\Plates\Engine as PlatesEngine;

class AssistedDigitalMiddlewareFactoryTest extends TestCase
{

    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCanInstantiate()
    {
        $factory = new AssistedDigitalMiddlewareFactory();
        $this->assertInstanceOf(AssistedDigitalMiddlewareFactory::class, $factory);
    }

    public function testFactoryWithEmptyConfig()
    {
        $factory = new AssistedDigitalMiddlewareFactory();

        $this->container->get( 'config' )->willReturn( [] );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/cookie/' );

        $factory($this->container->reveal());
    }

    public function testFactoryWithValidConfig()
    {
        $factory = new AssistedDigitalMiddlewareFactory();

        //---

        $this->container->get( LinkToken::class )->willReturn(
            $this->prophesize(LinkToken::class)->reveal()
        );

        $this->container->get( PlatesEngine::class )->willReturn(
            $this->prophesize(PlatesEngine::class)->reveal()
        );

        $this->container->get( 'config' )->willReturn([
            'ad' => [
                'cookie' => [
                    'name' => 'cookie-name'
                ]
            ]
        ]);

        //---

        $result = $factory($this->container->reveal());
        $this->assertInstanceOf(AssistedDigitalMiddleware::class, $result);
    }

}