<?php

declare(strict_types=1);

use App\Middleware;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Mezzio\Handler\NotFoundHandler;
use Laminas\Stratigility\Middleware\ErrorHandler;

/**
 * Setup middleware pipeline:
 */

/** @var \Mezzio\Application $app */

// The error handler should be the first (most outer) middleware to catch
// all Exceptions.
return function (
    \Mezzio\Application $app,
    \Mezzio\MiddlewareFactory $factory,
    \Psr\Container\ContainerInterface $container
) : void {
$app->pipe(ErrorHandler::class);
$app->pipe(ServerUrlMiddleware::class);

// Pipe more middleware here that you want to execute on every request:
// - bootstrapping
// - pre-conditions
// - modifications to outgoing responses
//
// Piped Middleware may be either callables or service names. Middleware may
// also be passed as an array; each item in the array must resolve to
// middleware eventually (i.e., callable or service name).
//
// Middleware can be attached to specific paths, allowing you to mix and match
// applications under a common domain.  The handlers in each middleware
// attached this way will see a URI with the MATCHED PATH SEGMENT REMOVED!!!
//
// - $app->pipe('/api', $apiMiddleware);
// - $app->pipe('/docs', $apiDocMiddleware);
// - $app->pipe('/files', $filesMiddleware);

// Register the routing middleware in the middleware pipeline
$app->pipe(RouteMiddleware::class);
$app->pipe(ImplicitHeadMiddleware::class);
$app->pipe(ImplicitOptionsMiddleware::class);
$app->pipe(\Mezzio\Router\Middleware\MethodNotAllowedMiddleware::class);
$app->pipe(UrlHelperMiddleware::class);

$app->pipe(Middleware\Session\SessionMiddleware::class);
$app->pipe(Middleware\Session\CsrfMiddleware::class);
$app->pipe(Middleware\Session\SlimFlashMiddleware::class);

// Authorization middleware to determine if the user can access the requested route
$app->pipe(Middleware\Authorization\AuthorizationMiddleware::class);

// Middleware to set any default data in the template renderer
$app->pipe(Middleware\ViewData\ViewDataMiddleware::class);

// Register the dispatch middleware in the middleware pipeline
$app->pipe(DispatchMiddleware::class);

// At this point, if no Response is return by any middleware, the
// NotFoundHandler kicks in; alternately, you can provide other fallback
// middleware to execute.
$app->pipe(NotFoundHandler::class);
};
