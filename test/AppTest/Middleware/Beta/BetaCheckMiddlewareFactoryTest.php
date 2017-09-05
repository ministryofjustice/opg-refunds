<?php
namespace AppTest\Middleware\Beta;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;

use App\Middleware\Beta\BetaCheckMiddleware;
use App\Middleware\Beta\BetaCheckMiddlewareFactory;

use Zend\Expressive\Template\TemplateRendererInterface;
use App\Service\Refund\Beta\BetaLinkChecker;

class BetaCheckMiddlewareFactoryTest extends TestCase
{

    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

    }

    public function testCanInstantiate()
    {
        $factory = new BetaCheckMiddlewareFactory( $this->container->reveal() );
        $this->assertInstanceOf(BetaCheckMiddlewareFactory::class, $factory);
    }

    public function testFactoryWithEmptyConfig()
    {
        $factory = new BetaCheckMiddlewareFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn( [] );

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/cookie/' );

        $factory($this->container->reveal());
    }

    public function testFactoryWithNoEnabledConfigConfig()
    {
        $factory = new BetaCheckMiddlewareFactory( $this->container->reveal() );

        $this->container->get( 'config' )->willReturn([
            'beta' => [
                'cookie' => [
                    'name' => 'cookie-name'
                ]
            ]
        ]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp( '/enabled/' );

        $factory($this->container->reveal());
    }


    public function testFactoryWithValidConfig()
    {
        $factory = new BetaCheckMiddlewareFactory( $this->container->reveal() );

        //---

        $this->container->get( BetaLinkChecker::class )->willReturn(
            $this->prophesize(BetaLinkChecker::class)->reveal()
        );

        $this->container->get( TemplateRendererInterface::class )->willReturn(
            $this->prophesize(TemplateRendererInterface::class)->reveal()
        );

        $this->container->get( 'config' )->willReturn([
            'beta' => [
                'enabled' => true,
                'cookie' => [
                    'name' => 'cookie-name'
                ]
            ]
        ]);

        //---

        $result = $factory($this->container->reveal());
        $this->assertInstanceOf(BetaCheckMiddleware::class, $result);
    }

}