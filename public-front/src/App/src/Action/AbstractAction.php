<?php
namespace App\Action;

use App\Service\Refund\FlowController;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AbstractAction implements
    RequestHandlerInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;

    protected function isActionAccessible(ServerRequestInterface $request)
    {
        $session = $request->getAttribute('session');

        $matchedRoute = $request->getAttribute('Mezzio\Router\RouteResult')->getMatchedRouteName();

        return FlowController::routeAccessible($matchedRoute, $session);
    }
}
