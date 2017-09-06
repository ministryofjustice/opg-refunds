<?php
namespace AppTest\Middleware\Beta;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use App\Middleware\Beta\BetaCheckMiddleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Service\Refund\Beta\BetaLinkChecker;

use Zend\Diactoros\Response\HtmlResponse;

class BetaCheckMiddlewareTest extends TestCase
{
    const TEST_HTML = '<HTML>';
    const TEST_COOKIE_NAME = 'cookie-name';
    const TEST_ERROR_SLUG = 'not-valid';

    private $request;
    private $delegateInterface;
    private $betaLinkChecker;
    private $templateRenderer;

    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->delegateInterface = $this->prophesize(DelegateInterface::class);
        $this->betaLinkChecker = $this->prophesize(BetaLinkChecker::class);
        $this->templateRenderer = $this->prophesize(TemplateRendererInterface::class);

        $this->betaLinkChecker->isLinkValid(Argument::any(),Argument::any(),Argument::any())
            ->willReturn(self::TEST_ERROR_SLUG);

        $this->request->getCookieParams()->willReturn([]);
        $this->request->getAttribute('Zend\Expressive\Router\RouteResult')->willReturn(null);
    }

    private function getInstance( bool $betaEnabled ){
        return new BetaCheckMiddleware(
            $this->templateRenderer->reveal(),
            $this->betaLinkChecker->reveal(),
            self::TEST_COOKIE_NAME,
            $betaEnabled
        );
    }

    public function testCanInstantiate()
    {
        $middleware = $this->getInstance(false);
        $this->assertInstanceOf(BetaCheckMiddleware::class, $middleware);
    }

    /**
     * Tests when beta is disabled
     */
    public function testNothingHappensWhenBetaIsDisabled()
    {
        $middleware = $this->getInstance(false);

        $request = $this->request->reveal();

        $this->request->getAttribute( Argument::any() )->shouldNotBeCalled();
        $this->delegateInterface->process( $request )->shouldBeCalled();

        $middleware->process(
            $request,
            $this->delegateInterface->reveal()
        );

    }

    /**
     * Tests when no cookie is passed
     */
    public function testWhenBetaIsEnabledWithNoCookie()
    {
        $middleware = $this->getInstance(true);

        $this->templateRenderer->render( Argument::type('string'),[ 'reason'=>'no-cookie' ] )
            ->willReturn( self::TEST_HTML )
            ->shouldBeCalled();

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(self::TEST_HTML, (string)$response->getBody());
    }

    /**
     * Tests when the passed cookie length is invalid
     */
    public function testWhenBetaIsEnabledWithInvalidLengthCookie()
    {
        $middleware = $this->getInstance(true);

        $this->templateRenderer->render( Argument::type('string'),[ 'reason'=>'no-cookie' ] )
            ->willReturn( self::TEST_HTML )
            ->shouldBeCalled();

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => 'invalid-cookie'
        ]);

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(self::TEST_HTML, (string)$response->getBody());
    }

    /**
     * Tests when the passed cookie content is invalid
     */
    public function testWhenBetaIsEnabledWithInvalidCookieContent()
    {
        $middleware = $this->getInstance(true);

        $this->templateRenderer->render( Argument::type('string'),[ 'reason'=>self::TEST_ERROR_SLUG ] )
            ->willReturn( self::TEST_HTML )
            ->shouldBeCalled();

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => 'invalid-cookie-content'
        ]);

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(self::TEST_HTML, (string)$response->getBody());
    }

    /**
     * Tests when the passed cookie is valid, but the link has already been used.
     */
    public function testWhenBetaIsEnabledWithValidCookieContentButLinkIsUsed()
    {
        $middleware = $this->getInstance(true);

        $this->templateRenderer->render( Argument::type('string'),[ 'reason'=>'link-used' ] )
            ->willReturn( self::TEST_HTML )
            ->shouldBeCalled();

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => 'valid-cookie-content'
        ]);

        $this->betaLinkChecker->isLinkValid(Argument::any(),Argument::any(),Argument::any())->willReturn(true);
        $this->betaLinkChecker->hasLinkBeenUsed(Argument::any())->willReturn(true);

        //---

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals(self::TEST_HTML, (string)$response->getBody());
    }

    /**
     * Tests when everything is valid
     */
    public function testWhenBetaIsEnabledWithValidCookieContentAndLinkIsUnused()
    {
        $middleware = $this->getInstance(true);

        $this->request->getCookieParams()->willReturn([
            self::TEST_COOKIE_NAME => 'valid-cookie-content'
        ]);

        $this->betaLinkChecker->isLinkValid(Argument::any(),Argument::any(),Argument::any())->willReturn(true);
        $this->betaLinkChecker->hasLinkBeenUsed(Argument::any())->willReturn(false);

        $this->request->withAttribute('betaId', Argument::type('string'))
            ->willReturn( $this->request->reveal() )
            ->shouldBeCalled();

        //---

        $middleware->process(
            $this->request->reveal(),
            $this->delegateInterface->reveal()
        );
    }

}