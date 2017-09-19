<?php
namespace App\Action;

use App\Service\Refund\FlowController;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

abstract class AbstractAction implements
    ServerMiddlewareInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;

    protected function isActionAccessible(ServerRequestInterface $request)
    {
        $session = $request->getAttribute('session');

        $matchedRoute = $request->getAttribute('Zend\Expressive\Router\RouteResult')->getMatchedRouteName();

        return FlowController::routeAccessible($matchedRoute, $session, $request->getAttribute('who'));
    }
}
