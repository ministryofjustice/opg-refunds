<?php
namespace AppTest\Middleware\Session;

use Prophecy\Argument;
use PHPUnit\Framework\TestCase;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Zend\Expressive\Router\RouteResult;
use Zend\Diactoros\Response as RealResponse;

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
        $this->sessionManager->delete( Argument::type('string') )->shouldBeCalled();

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

        $this->sessionManager->write( Argument::type('string'), Argument::type('array') )->shouldBeCalled();

        $middleware = new SessionMiddleware( $this->sessionManager->reveal(), 300 );

        $this->request->getCookieParams()->willReturn(array());

        // We use this to amend the data stored in session; as a delegate process would do.
        $this->request->withAttribute( 'session', Argument::that(function ($arg) {
            $arg['test'] = true;
            return true;
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
         * We are setting the cookie here, so we expect a correctly named cookie, with a value, that has not expired.
         */
        $this->assertEquals( SessionMiddleware::COOKIE_NAME, $cookie->getName() );
        $this->assertFalse( $cookie->isExpired() );
        $this->assertTrue( $cookie->getSecure() );
        $this->assertTrue( $cookie->getHttpOnly() );
        $this->assertInternalType( 'string', $cookie->getValue() );

        // We're expecting the session ID to be over 75 characters.
        $this->assertGreaterThan( 75, strlen($cookie->getValue()) );
    }


    /**
     * Test for:
     *  - An initial cookie is set.
     *  - Some data is returned from the Session Manager.
     *  - No new data is added.
     *
     *  Thus we expect a valid session cookie to be returned with the same value.
     */
    public function testWithCookieThatReturnsData()
    {
        $cookieValue = 'cookie-value';

        $testData = [
            'test'=>true
        ];

        //---

        $this->sessionManager->read( $cookieValue )->willReturn($testData);
        $this->sessionManager->write( $cookieValue, $testData )->shouldBeCalled();

        $middleware = new SessionMiddleware( $this->sessionManager->reveal(), 300 );

        $this->request->getCookieParams()->willReturn([
            SessionMiddleware::COOKIE_NAME => $cookieValue
        ]);

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
         * We are setting the cookie here, so we expect a correctly named
         * cookie, with the above value, that has not expired.
         */
        $this->assertEquals( SessionMiddleware::COOKIE_NAME, $cookie->getName() );
        $this->assertFalse( $cookie->isExpired() );
        $this->assertTrue( $cookie->getSecure() );
        $this->assertTrue( $cookie->getHttpOnly() );
        $this->assertInternalType( 'string', $cookie->getValue() );
        $this->assertEquals( $cookieValue, $cookie->getValue() );
    }


    /**
     * Test for:
     *  - An initial cookie is set.
     *  - No data is returned from the Session Manager.
     *  - Some new data is added.
     *
     *  Thus we expect a valid session cookie to be returned with a different cookie value.
     */
    public function testWithCookieThatNewDataIsStoredFor()
    {
        $cookieValue = 'cookie-value';

        // Return an empty array.
        $this->sessionManager->read( $cookieValue )->willReturn([]);

        // We expect the new session to be written
        $this->sessionManager->write( Argument::type('string'), ['test' => true] )->shouldBeCalled();

        $middleware = new SessionMiddleware( $this->sessionManager->reveal(), 300 );

        $this->request->getCookieParams()->willReturn([
            SessionMiddleware::COOKIE_NAME => $cookieValue
        ]);

        // We use this to set data in session; as a delegate process would do.
        $this->request->withAttribute( 'session', Argument::that(function ($arg) {
            $arg['test'] = true;
            return true;
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
         * We are setting an invalid cookie name here, so we expect a correctly named
         * cookie, with a *different* value, that has not expired.
         */
        $this->assertEquals( SessionMiddleware::COOKIE_NAME, $cookie->getName() );
        $this->assertFalse( $cookie->isExpired() );
        $this->assertTrue( $cookie->getSecure() );
        $this->assertTrue( $cookie->getHttpOnly() );
        $this->assertInternalType( 'string', $cookie->getValue() );
        $this->assertNotEquals( $cookieValue, $cookie->getValue() );

        // We're expecting the session ID to be over 75 characters.
        $this->assertGreaterThan( 75, strlen($cookie->getValue()) );
    }
}
