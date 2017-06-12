<?php
namespace AppTest\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

use Zend\Expressive\Router\RouteResult;

use App\Middleware\CacheControlMiddleware;

class CacheControlMiddlewareTest extends TestCase
{

    private $request;
    private $routeResult;
    private $delegateInterface;

    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->routeResult = $this->prophesize(RouteResult::class);
        $this->delegateInterface = $this->prophesize(DelegateInterface::class);
    }

    public function testCanInstantiate()
    {
        $middleware = new CacheControlMiddleware();
        $this->assertInstanceOf(CacheControlMiddleware::class, $middleware);
    }

    public function testNormalCase()
    {
        $middleware = new CacheControlMiddleware();

        $this->routeResult->getMatchedRouteName()->willReturn( 'eligibility.test' );

        $this->request->getAttribute(RouteResult::class)->willReturn( $this->routeResult->reveal() );

        $this->delegateInterface->process()->willReturn(
            $this->prophesize( ResponseInterface::class )->reveal()
        );

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

    }

}
