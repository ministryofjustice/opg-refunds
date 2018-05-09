<?php
namespace AppTest\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

use Prophecy\Argument;

use Zend\Expressive\Router\RouteResult;
use Zend\Diactoros\Response as RealResponse;

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

    /**
     * Check caching headers are added when expected.
     */
    public function testOnEligibilityPage()
    {
        $middleware = new CacheControlMiddleware();

        $this->routeResult->getMatchedRouteName()->willReturn( 'home' );

        $this->request->getAttribute(RouteResult::class)->willReturn( $this->routeResult->reveal() );

        $this->delegateInterface->process( Argument::type(ServerRequestInterface::class) )->willReturn(
            /*
             * We're using a real response here as the elaborate mocking
             * required not to does not justify the risk of using a concrete object.
             */
            new RealResponse
        );

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $headers = $response->getHeaders();

        //---

        // Checks we have a Cache-Control header, with one value, set to what we expect.

        $this->assertArrayHasKey('Cache-Control', $headers);

        $cacheControl = $headers['Cache-Control'];

        $this->assertInternalType('array', $cacheControl);
        $this->assertCount(1, $cacheControl);

        $cacheControl = array_pop($cacheControl);

        $this->assertEquals( 'max-age='.CacheControlMiddleware::MAX_AGE, $cacheControl );

        //---

        // Checks we have a Expires header, with one value, set (within 10 seconds) to what we expect.

        $this->assertArrayHasKey('Expires', $headers);

        $expires = $headers['Expires'];

        $this->assertInternalType('array', $expires);
        $this->assertCount(1, $expires);

        $expires = strtotime(array_pop($expires));

        $expected = time() + CacheControlMiddleware::MAX_AGE;

        $this->assertLessThan($expected+10, $expires);
        $this->assertGreaterThan($expected-10, $expires);
    }

    /**
     * Tests that if no (null) Response is returned form the delegate,
     * that we don't attempt to apply any headers.
     */
    public function testWithoutResponse()
    {
        $middleware = new CacheControlMiddleware();

        $this->routeResult->getMatchedRouteName()->willReturn( 'eligibility.test' );

        $this->request->getAttribute(RouteResult::class)->willReturn( $this->routeResult->reveal() );

        $this->delegateInterface->process( Argument::type(ServerRequestInterface::class) )->willReturn(null);

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertNull($response);
    }

    /**
     * Ensure we only add headers to pages who's route name starts with 'eligibility.'.
     */
    public function testWithoutEligibilityPrefix()
    {
        $middleware = new CacheControlMiddleware();

        $this->routeResult->getMatchedRouteName()->willReturn( 'apply.test' );

        $this->request->getAttribute(RouteResult::class)->willReturn( $this->routeResult->reveal() );

        $this->delegateInterface->process( Argument::type(ServerRequestInterface::class) )->willReturn(
        /*
         * We're using a real response here as the elaborate mocking
         * required not to does not justify the risk of using a concrete object.
         */
            new RealResponse
        );

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $headers = $response->getHeaders();

        //---

        // Checks we have a Cache-Control header, with one value, set to what we expect.

        $this->assertArrayHasKey('Cache-Control', $headers);

        $cacheControl = $headers['Cache-Control'];

        $this->assertInternalType('array', $cacheControl);
        $this->assertCount(1, $cacheControl);

        $cacheControl = array_pop($cacheControl);

        $this->assertEquals( 'no-store', $cacheControl );

        //---

        // Checks we have a Pragma header, with one value, set to what we expect.

        $this->assertArrayHasKey('Pragma', $headers);

        $pragma = $headers['Pragma'];

        $this->assertInternalType('array', $pragma);
        $this->assertCount(1, $pragma);

        $pragma = array_pop($pragma);

        $this->assertEquals( 'no-cache', $pragma );
    }
}
