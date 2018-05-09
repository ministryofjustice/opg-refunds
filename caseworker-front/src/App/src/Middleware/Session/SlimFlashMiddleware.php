<?php

namespace App\Middleware\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use UnexpectedValueException;

/**
 * http://zendframework.github.io/zend-expressive/cookbook/flash-messengers/
 *
 * Class SlimFlashMiddleware
 * @package App\Middleware\Session
 */
class SlimFlashMiddleware implements ServerMiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        if (!isset($session)) {
            throw new UnexpectedValueException('Session required');
        }

        return $delegate->process(
            $request->withAttribute('flash', new Messages())
        );
    }
}