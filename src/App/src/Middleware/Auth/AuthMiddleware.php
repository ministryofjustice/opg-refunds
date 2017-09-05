<?php

namespace App\Middleware\Auth;

use App\Service\Session\Session;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Adds support for sessions via the injected SessionManager.
 *
 * Class SessionMiddleware
 * @package App\Middleware\Session
 */
class AuthMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * AuthMiddleware constructor
     *
     * @param UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  If the session isn't authenticated redirect to the sign in screen
        $session = $request->getAttribute('session');

        if (!$session instanceof Session || !$session->loggedIn()) {
            return new RedirectResponse(
                $this->urlHelper->generate('sign.in')
            );
        }

        return $delegate->process($request);
    }
}
