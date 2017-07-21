<?php
namespace App\Middleware\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use App\Service\Session\SessionManager;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

use Zend\Math\BigInteger\BigInteger;

use DateTime;
use ArrayObject;

/**
 * Adds support for sessions via the injected SessionManager.
 *
 * Class SessionMiddleware
 * @package App\Middleware\Session
 */
class SessionMiddleware implements ServerMiddlewareInterface
{

    const COOKIE_NAME = 'rs';
    const COOKIE_PATH = '/application';

    /**
     * @var int Time in seconds before the session cookie should expire.
     */
    private $sessionTTL;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    public function __construct(SessionManager $sessionManager, int $sessionTTL)
    {
        $this->sessionTTL = $sessionTTL;
        $this->sessionManager = $sessionManager;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $cookies = $request->getCookieParams();

        $session = new ArrayObject;

        // Check for a session cookie and init the session if we have one.
        if (isset($cookies[self::COOKIE_NAME])) {
            $sessionId = $cookies[self::COOKIE_NAME];

            $sessionArray = $this->sessionManager->read($sessionId);

            $session->exchangeArray($sessionArray);

            if ($session->count() == 0) {
                // Non-existent / empty sessions get a new ID.
                $sessionId = $this->generateSessionId();
            }
        } else {
            // Else we need to start a new ID.
            $sessionId = $this->generateSessionId();
        }

        //---

        $response = $delegate->process(
            $request->withAttribute('session', $session)
        );

        //---

        // If we have data to store in the session, do it.
        if ($session->count() > 0 && $response instanceof ResponseInterface) {
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

            // And wipe the stored data.
            $this->sessionManager->delete($sessionId);
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
