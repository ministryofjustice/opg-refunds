<?php

namespace App\Middleware\Auth;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Authentication\AuthenticationService;
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
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * AuthMiddleware constructor
     *
     * @param AuthenticationService $authService
     * @param UrlHelper $urlHelper
     */
    public function __construct(AuthenticationService $authService, UrlHelper $urlHelper)
    {
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $identity = $this->authService->getIdentity();

        if (!is_null($identity)) {
            //  Pass the identity in the request for convenience
            return $delegate->process(
                $request->withAttribute('identity', $identity)
            );
        }

        return new RedirectResponse($this->urlHelper->generate('sign.in'));
    }
}
