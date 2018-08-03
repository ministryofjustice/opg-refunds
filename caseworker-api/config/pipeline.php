<?php

declare(strict_types=1);

use App\Middleware;
use Auth\Middleware as AuthMiddleware;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Router\Middleware\DispatchMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Router\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Router\Middleware\RouteMiddleware;
use Zend\Expressive\Handler\NotFoundHandler;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * Setup middleware pipeline:
 */

/** @var \Zend\Expressive\Application $app */

// The error handler should be the first (most outer) middleware to catch
// all Exceptions.
return function (
    \Zend\Expressive\Application $app,
    \Zend\Expressive\MiddlewareFactory $factory,
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
$app->pipe(\Zend\Expressive\Router\Middleware\MethodNotAllowedMiddleware::class);
$app->pipe(BodyParamsMiddleware::class);

//  Handle any API problem exception types
$app->pipe(Middleware\ProblemDetailsMiddleware::class);

// Authorization middleware to determine if the user can access the requested route
$app->pipe(AuthMiddleware\AuthorizationMiddleware::class);

// Register the dispatch middleware in the middleware pipeline
$app->pipe(DispatchMiddleware::class);

// At this point, if no Response is return by any middleware, the
// NotFoundHandler kicks in; alternately, you can provide other fallback
// middleware to execute.
$app->pipe(NotFoundHandler::class);
};
