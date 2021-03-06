<?php
namespace App\Middleware\Tracking;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;

use League\Plates\Engine as PlatesEngine;

/**
 * Creates and adds into Plates an anonymous tracking token.
 *
 * Class TrackingMiddleware
 * @package App\Middleware\AssistedDigital
 */
class TrackingMiddleware implements ServerMiddlewareInterface
{
    private $plates;

    public function __construct(PlatesEngine $plates)
    {
        $this->plates = $plates;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $session = $request->getAttribute('session');

        if (isset($session)) {
            if (!isset($session['meta']['tracking'])) {
                $session['meta']['tracking'] = bin2hex(random_bytes(16));
            }

            $this->plates->addData([
                'tracking' => $session['meta']['tracking']
            ]);
        }

        return $delegate->handle($request);
    }
}
