<?php

use App\Middleware;
use Auth\Middleware as AuthMiddleware;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Middleware\ImplicitHeadMiddleware;
use Zend\Expressive\Middleware\ImplicitOptionsMiddleware;
use Zend\Expressive\Middleware\NotFoundHandler;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * Setup middleware pipeline:
 */

// The error handler should be the first (most outer) middleware to catch
// all Exceptions.
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
$app->pipeRoutingMiddleware();
$app->pipe(ImplicitHeadMiddleware::class);
$app->pipe(ImplicitOptionsMiddleware::class);
$app->pipe(BodyParamsMiddleware::class);

//  Handle any API problem exception types
$app->pipe(Middleware\ProblemDetailsMiddleware::class);

//  Add middleware to verify on the cases path
foreach (['/v1/cases'] as $path) {
    $app->pipe($path, [
        AuthMiddleware\AuthenticationMiddleware::class,
    ]);
}

// Register the dispatch middleware in the middleware pipeline
$app->pipeDispatchMiddleware();

// At this point, if no Response is return by any middleware, the
// NotFoundHandler kicks in; alternately, you can provide other fallback
// middleware to execute.
$app->pipe(NotFoundHandler::class);
