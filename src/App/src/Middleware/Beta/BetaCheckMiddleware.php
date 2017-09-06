<?php
namespace App\Middleware\Beta;

use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Response;
use Zend\Expressive\Template\TemplateRendererInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

use App\Service\Refund\Beta\BetaLinkChecker;

class BetaCheckMiddleware implements ServerMiddlewareInterface
{
    private $checker;
    private $cookieName;
    private $betaEnabled;
    private $templateRenderer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        BetaLinkChecker $checker,
        string $cookieName,
        bool $betaEnabled
    ) {
        $this->checker = $checker;
        $this->betaEnabled = $betaEnabled;
        $this->cookieName = $cookieName;
        $this->templateRenderer = $templateRenderer;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($this->betaEnabled !== true) {
            return $delegate->process($request);
        }

        //---

        $routeResult = $request->getAttribute('Zend\Expressive\Router\RouteResult');

        // If they are accessing the beta landing page, always allow the action to be called.
        // Also allow access to the done page.
        if (isset($routeResult) && in_array($routeResult->getMatchedRouteName(), ['beta', 'apply.done'])) {
            return $delegate->process($request);
        }

        //---

        $cookies = $request->getCookieParams();

        // Check we have the cookie...
        if (isset($cookies[$this->cookieName])) {
            $values = explode('-', $cookies[$this->cookieName]);

            // Which contains the 3 expected bits of data...
            if (count($values) == 3) {
                $isValid = $this->checker->isLinkValid($values[0], $values[1], $values[2]);

                // Which represents a valid link...
                if ($isValid === true) {
                    $alreadyUsed = $this->checker->hasLinkBeenUsed($values[0]);

                    // And has not already been used...
                    if ($alreadyUsed === false) {
                        // If so, they can continue.
                        return $delegate->process(
                            // Include the betaId in the request
                            $request->withAttribute('betaId', $values[0])
                        );
                    }

                    $isValid = 'link-used';
                }
            }
        }

        //---

        // Display the error
        return new Response\HtmlResponse( $this->templateRenderer->render('app::beta-page', [
            'reason' => isset($isValid) ? $isValid : 'no-cookie'
        ]) );
    }
}
