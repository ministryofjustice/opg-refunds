<?php

namespace App\Middleware\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
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
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        if (!isset($session)) {
            throw new UnexpectedValueException('Session required');
        }

        if (!isset($session['meta']['csrf'])) {
            $session['meta']['csrf'] = $this->generateSecret();
        }

        return $delegate->process($request);
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
