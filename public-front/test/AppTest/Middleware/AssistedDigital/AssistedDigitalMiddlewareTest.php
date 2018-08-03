<?php
namespace AppTest\Middleware\AssistedDigital;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use App\Middleware\AssistedDigital\AssistedDigitalMiddleware;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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

        $this->delegateInterface->handle( Argument::type(ServerRequestInterface::class) )->willReturn(
            $this->prophesize(ResponseInterface::class)->reveal()
        );
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

        $this->request->withAttribute( 'isDonorDeceased', false )->willReturn($this->request)->shouldBeCalled();

        // And an empty 'ad' array should be set in plates.
        $this->platesEngine->addData([
            'ad' => [],
            'isDonorDeceased' => false
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

        $this->request->withAttribute( 'isDonorDeceased', false )->willReturn($this->request)->shouldBeCalled();


        // And an empty 'ad' array should be set in plates.
        $this->platesEngine->addData([
            'ad' => [],
            'isDonorDeceased' => false
        ])->shouldBeCalled();

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }

    public function testWithValidCookieSetDonorDeceased()
    {
        $middleware = $this->getInstance();

        $cookieValue = 'valid-cookie-value';

        $payload = [ 'user'=>123, 'type'=>'donor_deceased' ];

        //---

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => $cookieValue
        ]);

        //---

        $this->linkChecker->verify( $cookieValue )->willReturn($payload)->shouldBeCalled();

        $this->request->withAttribute( 'ad', $payload )->willReturn($this->request->reveal())->shouldBeCalled();

        $this->request->withAttribute( 'isDonorDeceased', true )->willReturn($this->request)->shouldBeCalled();

        $this->platesEngine->addData([
            'ad' => $payload,
            'isDonorDeceased' => true
        ])->shouldBeCalled();

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }

    public function testWithValidCookieSetAssistedDigital()
    {
        $middleware = $this->getInstance();

        $cookieValue = 'valid-cookie-value';

        $payload = [ 'user'=>123, 'type'=>'assisted_digital' ];

        //---

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => $cookieValue
        ]);

        //---

        $this->linkChecker->verify( $cookieValue )->willReturn($payload)->shouldBeCalled();

        $this->request->withAttribute( 'ad', $payload )->willReturn($this->request->reveal())->shouldBeCalled();

        $this->request->withAttribute( 'isDonorDeceased', false )->willReturn($this->request)->shouldBeCalled();

        $this->platesEngine->addData([
            'ad' => $payload,
            'isDonorDeceased' => false
        ])->shouldBeCalled();

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }
}
