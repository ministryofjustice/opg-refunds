<?php

namespace App\Middleware\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Session\SessionManager;

/**
 * Adds support for sessions via the injected SessionManager.
 *
 * Class SessionMiddleware
 * @package App\Middleware\Session
 */
class SessionMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * SessionMiddleware constructor
     *
     * @param SessionManager $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface|static
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Pass the session storage in the request for convenience
        $session = $this->sessionManager->getStorage();

        return $delegate->process(
            $request->withAttribute('session', $session)
        );
    }
}
