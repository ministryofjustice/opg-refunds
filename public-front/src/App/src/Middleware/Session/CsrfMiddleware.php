<?php
namespace App\Middleware\Session;

use UnexpectedValueException;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;

use ParagonIE\ConstantTime;

/**
 * Injects a CSRF secret into the session.
 *
 * Class CsrfMiddleware
 * @package App\Middleware\Session
 */
class CsrfMiddleware implements ServerMiddlewareInterface
{

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
     * @throws \Exception
     */
    private function generateSecret() : string
    {
        return ConstantTime\Base64UrlSafe::encode(random_bytes(64));
    }
}
