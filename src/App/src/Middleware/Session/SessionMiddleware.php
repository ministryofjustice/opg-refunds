<?php

namespace App\Middleware\Session;

use App\Service\Session\Session;
use App\Service\Session\SessionManager;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Math\BigInteger\BigInteger;
use DateTime;

/**
 * Adds support for sessions via the injected SessionManager.
 *
 * Class SessionMiddleware
 * @package App\Middleware\Session
 */
class SessionMiddleware implements ServerMiddlewareInterface
{
    const COOKIE_PATH = '/';
    const COOKIE_NAME = 'rs';

    /**
     * @var int Time in seconds before the session cookie should expire.
     */
    private $sessionTTL;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * SessionMiddleware constructor
     *
     * @param SessionManager $sessionManager
     * @param int $sessionTTL
     */
    public function __construct(SessionManager $sessionManager, int $sessionTTL)
    {
        $this->sessionTTL = $sessionTTL;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface|static
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $cookies = $request->getCookieParams();

        $session = new Session;

        // Check for a session cookie and init the session if we have one.
        if (isset($cookies[self::COOKIE_NAME])) {
            $sessionId = $cookies[self::COOKIE_NAME];

            $sessionArray = $this->sessionManager->read($sessionId);

            if (is_array($sessionArray) && count($sessionArray) > 0) {
                $session->exchangeArray($sessionArray);
            } else {
                // Else remove the ID
                // A new ID will be generated later if needed.
                unset($sessionId);
            }
        }

        //---

        $response = $delegate->process(
            $request->withAttribute('session', $session)
        );

        //---

        // If we have data to store in the session, do it.
        if ($session->count() > 0 && $response instanceof ResponseInterface) {
            // Test if we need a new session ID.
            if (!isset($sessionId)) {
                $sessionId = $this->generateSessionId();
            }

            $this->sessionManager->write($sessionId, $session->getArrayCopy());

            // Set a cookie with the session ID.
            $response = FigResponseCookies::set($response, SetCookie::create(self::COOKIE_NAME)
                ->withValue($sessionId)
                ->withSecure(true)
                ->withHttpOnly(true)
                ->withPath(self::COOKIE_PATH)
                ->withExpires(new DateTime("+{$this->sessionTTL} seconds")));

            // Add a strict SameSite value to the cookie
            $response = $response->withHeader(
                'Set-Cookie', $response->getHeader('Set-Cookie')[0].'; SameSite=strict'
            );

        } elseif ($response instanceof ResponseInterface) {
            // If there's no data to store, kill the cookie.
            $response = FigResponseCookies::set($response, SetCookie::createExpired(self::COOKIE_NAME)
                ->withPath(self::COOKIE_PATH));

            if (isset($sessionId)) {
                // Wipe the stored data.
                $this->sessionManager->delete($sessionId);
            }
        }

        //---

        return $response;
    }

    /**
     * Returns a randomly generated session id.
     *
     * @return string
     */
    private function generateSessionId() : string
    {
        return BigInteger::factory('bcmath')->baseConvert(
            bin2hex(random_bytes(64)),
            16,
            62
        );
    }
}
