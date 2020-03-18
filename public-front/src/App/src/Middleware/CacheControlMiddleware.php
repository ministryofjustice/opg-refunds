<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;

class CacheControlMiddleware implements ServerMiddlewareInterface
{

    /**
     * Time in seconds that non-personalised responses can be cached.
     */
    const MAX_AGE = 300;

    private $cacheablePages = [
        'home',
        'terms',
        'cookies',
        'contact',
        'start',
        'eligibility.when',
        'eligibility.when.answer',
        'eligibility.donor.deceased',
    ];

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {

        $response = $delegate->handle($request);

        $route = $request->getAttribute('Mezzio\Router\RouteResult');

        if (is_null($route)) {
            // No app route matched, thus don't add caching
            return $response;
        }

        // Return the current route name
        $matchedRoute = $route->getMatchedRouteName();

        // If it's an eligibility route
        if ($response instanceof ResponseInterface) {
            if (in_array($matchedRoute, $this->cacheablePages)) {
                // Allow caching on these pages
                $response = $response->withHeader('Cache-Control', 'max-age='.self::MAX_AGE)
                    ->withHeader('Expires', gmdate('D, d M Y H:i:s T', time() + self::MAX_AGE));
            } else {
                // Otherwise disable caching.
                $response = $response->withHeader('Cache-Control', 'no-store')
                    ->withHeader('Pragma', 'no-cache');
            }
        }

        return $response;
    }
}
