<?php
namespace AppTest\Middleware\Session;

use PHPUnit\Framework\TestCase;

use Interop\Container\ContainerInterface;

use App\Service\Session\SessionManager;
use App\Middleware\Session\SessionMiddleware;
use App\Middleware\Session\SessionMiddlewareFactory;

class SessionMiddlewareFactoryTest extends TestCase
{

    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCanInstantiate()
    {
        $factory = new SessionMiddlewareFactory();
        $this->assertInstanceOf(SessionMiddlewareFactory::class, $factory);
    }

    public function testWithoutConfiguration()
    {
        $factory = new SessionMiddlewareFactory();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/TTL/' );

        $factory($this->container->reveal());
    }

    public function testWithConfiguration()
    {
        $factory = new SessionMiddlewareFactory();

        $this->container->get( 'config' )->willReturn( ['session'=>['ttl'=>300]] );

        $this->container->get( SessionManager::class )->shouldBeCalled();
        $this->container->get( SessionManager::class )->willReturn(
            $this->prophesize(SessionManager::class)
        );

        $result = $factory($this->container->reveal());

        $this->assertInstanceOf(SessionMiddleware::class, $result);
    }

}