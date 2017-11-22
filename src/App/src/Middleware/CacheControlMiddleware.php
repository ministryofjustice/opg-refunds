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

    private $cachabePages = [
        'home',
        'terms',
        'cookies',
        'contact',
        'eligibility.when',
        'eligibility.when.answer',
    ];

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $response = $delegate->process($request);

        $route = $request->getAttribute('Zend\Expressive\Router\RouteResult');

        if (is_null($route)) {
            // No app route matched, thus don't add caching
            return $response;
        }

        // Return the current route name
        $matchedRoute = $route->getMatchedRouteName();

        // If it's an eligibility route
        if ($response instanceof ResponseInterface) {
            if (in_array($matchedRoute, $this->cachabePages)) {
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
