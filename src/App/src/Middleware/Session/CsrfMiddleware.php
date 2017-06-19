<?php
namespace App\Middleware\Session;

use UnexpectedValueException;

use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

use Zend\Math\BigInteger\BigInteger;

/**
 * Injects a Csrf secret into the session.
 *
 * Class CsrfMiddleware
 * @package App\Middleware\Session
 */
class CsrfMiddleware implements ServerMiddlewareInterface
{

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
