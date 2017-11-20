<?php
namespace AppTest\Middleware\AssistedDigital;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use App\Middleware\AssistedDigital\AssistedDigitalMiddleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

use App\Service\Refund\AssistedDigital\LinkToken;
use League\Plates\Engine as PlatesEngine;

class AssistedDigitalMiddlewareTest extends TestCase
{

    const TEST_COOKIE_NAME = 'cookie-name';

    private $request;
    private $delegateInterface;
    private $linkChecker;
    private $platesEngine;

    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->delegateInterface = $this->prophesize(DelegateInterface::class);
        $this->linkChecker = $this->prophesize(LinkToken::class);
        $this->platesEngine = $this->prophesize(PlatesEngine::class);

        $this->request->getCookieParams()->willReturn([]);
    }

    private function getInstance()
    {
        return new AssistedDigitalMiddleware(
            $this->linkChecker->reveal(),
            self::TEST_COOKIE_NAME,
            $this->platesEngine->reveal()
        );
    }

    public function testCanInstantiate()
    {
        $middleware = $this->getInstance();
        $this->assertInstanceOf(AssistedDigitalMiddleware::class, $middleware);
    }

    public function testWhenNoCookieSet()
    {
        $middleware = $this->getInstance();

        $this->linkChecker->verify( Argument::any() )->shouldNotBeCalled();

        $this->request->withAttribute( 'ad', Argument::any() )->shouldNotBeCalled();

        // And an empty 'ad' array should be set in plates.
        $this->platesEngine->addData([
            'ad' => []
        ])->shouldBeCalled();

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }

    public function testWithInvalidCookieSet()
    {
        $middleware = $this->getInstance();

        $cookieValue = 'invalid-cookie-value';

        //---

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => $cookieValue
        ]);

        //---

        $this->linkChecker->verify( $cookieValue )
            ->willThrow(new \UnexpectedValueException) // This exception informs the validation failed.
            ->shouldBeCalled();

        $this->request->withAttribute( 'ad', Argument::any() )->shouldNotBeCalled();


        // And an empty 'ad' array should be set in plates.
        $this->platesEngine->addData([
            'ad' => []
        ])->shouldBeCalled();

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }

    public function testWithValidCookieSet()
    {
        $middleware = $this->getInstance();

        $cookieValue = 'valid-cookie-value';

        $payload = [ 'user'=>123 ];

        //---

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => $cookieValue
        ]);

        //---

        $this->linkChecker->verify( $cookieValue )->willReturn($payload)->shouldBeCalled();

        $this->request->withAttribute( 'ad', $payload )->willReturn($this->request->reveal())->shouldBeCalled();

        $this->platesEngine->addData([
            'ad' => $payload
        ])->shouldBeCalled();

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }
}
