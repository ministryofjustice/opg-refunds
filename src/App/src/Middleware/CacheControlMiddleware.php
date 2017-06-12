<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

class CacheControlMiddleware implements ServerMiddlewareInterface
{

    /**
     * Time in seconds that non-personalised responses can be cached.
     */
    const MAX_AGE = 300;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $response = $delegate->process($request);

        // Return the current route name
        $matchedRoute = $request->getAttribute('Zend\Expressive\Router\RouteResult')->getMatchedRouteName();

        // If it's an eligibility route
        if ($response instanceof ResponseInterface && substr($matchedRoute, 0, 12) === 'eligibility.') {
            // Add caching headers
            $response = $response->withHeader( 'Cache-Control', 'max-age='.self::MAX_AGE )
                                 ->withHeader( 'Expires', gmdate('D, d M Y H:i:s T', time() + self::MAX_AGE ));
        }

        return $response;
    }
}
