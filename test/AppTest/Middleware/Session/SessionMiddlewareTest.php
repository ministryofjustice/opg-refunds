<?php
namespace AppTest\Middleware\Session;

use Prophecy\Argument;
use PHPUnit\Framework\TestCase;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Zend\Expressive\Router\RouteResult;
use Zend\Diactoros\Response as RealResponse;
use Zend\Diactoros\ServerRequest as RealRequest;

use App\Service\Session\SessionManager;
use App\Middleware\Session\SessionMiddleware;

use GuzzleHttp\Cookie\SetCookie as GuzzleSetCookie;

class SessionMiddlewareTest extends TestCase
{

    private $request;
    private $routeResult;
    private $delegateInterface;

    private $sessionManager;

    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->routeResult = $this->prophesize(RouteResult::class);
        $this->delegateInterface = $this->prophesize(DelegateInterface::class);

        $this->request->withAttribute( 'session', Argument::type('\ArrayObject') )->willReturn(
            $this->prophesize(ServerRequestInterface::class)->reveal()
        );

        $this->delegateInterface->process( Argument::type(ServerRequestInterface::class) )->willReturn(
            new RealResponse
        );

        $this->sessionManager = $this->prophesize(SessionManager::class);
    }

    public function testCanInstantiate()
    {
        $middleware = new SessionMiddleware( $this->sessionManager->reveal(), 300 );
        $this->assertInstanceOf(SessionMiddleware::class, $middleware);
    }


    /**
     * Test for:
     *  - No initial cookie is set.
     *  - No data is added to be stored in the session.
     *
     *  Thus we expect an expired, empty, cookie to be returned.
     */
    public function testWithoutCookieSetAndNoDataSet()
    {
        $middleware = new SessionMiddleware( $this->sessionManager->reveal(), 300 );

        $this->request->getCookieParams()->willReturn(array());

        // We are expecting an empty ArrayObject to be stored in the request.
        $this->request->withAttribute( 'session', Argument::that(function ($arg) {
            return ($arg instanceof \ArrayObject && $arg->count() == 0);
        }))->shouldBeCalled()->willReturn(
            $this->prophesize(ServerRequestInterface::class)->reveal()
        );

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Set-Cookie', $headers);

        $cookies = $headers['Set-Cookie'];

        $this->assertInternalType('array', $cookies);
        $this->assertCount(1, $cookies);


        $cookie = GuzzleSetCookie::fromString(array_pop($cookies));

        /*
         * We are removing the cookie here, so we expect a correctly named cookie, with no value, that has expired.
         */
        $this->assertEquals( SessionMiddleware::COOKIE_NAME, $cookie->getName() );
        $this->assertTrue( $cookie->isExpired() );
        $this->assertEmpty( $cookie->getValue() );
    }

    /**
     * Test for:
     *  - No initial cookie is set.
     *  - New data is added to be stored in the session.
     *
     *  Thus we expect a valid session cookie to be returned.
     */
    public function testWithoutCookieSetAndNewDataSet()
    {

        $middleware = new SessionMiddleware( $this->sessionManager->reveal(), 300 );


    }

}