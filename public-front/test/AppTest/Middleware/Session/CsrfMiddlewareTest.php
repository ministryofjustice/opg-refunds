<?php
namespace AppTest\Middleware\Session;

use ArrayObject;

use Prophecy\Argument;

use PHPUnit\Framework\TestCase;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use App\Middleware\Session\CsrfMiddleware;

class CsrfMiddlewareTest extends TestCase
{

    private $request;
    private $delegateInterface;

    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->delegateInterface = $this->prophesize(DelegateInterface::class);

        $this->delegateInterface->handle( Argument::type(ServerRequestInterface::class) )->willReturn(
            $this->prophesize(ResponseInterface::class)->reveal()
        );
    }

    public function testCanInstantiate()
    {
        $middleware = new CsrfMiddleware();
        $this->assertInstanceOf(CsrfMiddleware::class, $middleware);
    }

    public function testWithoutPassedSession()
    {

        $middleware = new CsrfMiddleware();

        $this->expectException(\UnexpectedValueException::class);

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }

    public function testWithPassedSessionWithoutToken()
    {

        $middleware = new CsrfMiddleware();

        $session = new ArrayObject;

        $this->request->getAttribute( 'session' )->willReturn($session);

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertArrayHasKey('meta', $session);
        $this->assertArrayHasKey('csrf', $session['meta']);

        $this->assertInternalType('string', $session['meta']['csrf']);
        $this->assertGreaterThan( 75, strlen($session['meta']['csrf']));
    }

    public function testWithPassedSessionWithToken()
    {
        $token = 'csrf-token';

        $middleware = new CsrfMiddleware();

        $session = new ArrayObject([
            'meta' => [
                'csrf' => $token
            ]
        ]);

        $this->request->getAttribute( 'session' )->willReturn($session);

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertArrayHasKey('meta', $session);
        $this->assertArrayHasKey('csrf', $session['meta']);

        $this->assertEquals( $token, $session['meta']['csrf']);
    }

}