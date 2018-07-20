<?php

namespace App\Middleware\Session;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Math\BigInteger\BigInteger;
use UnexpectedValueException;

/**
 * Injects a CSRF secret into the session.
 *
 * Class CsrfMiddleware
 * @package App\Middleware\Session
 */
class CsrfMiddleware implements ServerMiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $session = $request->getAttribute('session');

        if (!isset($session)) {
            throw new UnexpectedValueException('Session required');
        }

        if (!isset($session['meta']['csrf'])) {
            $session['meta']['csrf'] = $this->generateSecret();
        }

        return $delegate->handle($request);
    }

    /**
     * Returns a randomly generated session id.
     *
     * @return string
     */
    private function generateSecret() : string
    {
        return BigInteger::factory('bcmath')->baseConvert(
            bin2hex(random_bytes(64)),
            16,
            62
        );
    }
}
