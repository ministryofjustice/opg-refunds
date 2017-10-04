<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response;

/**
 * Class AbstractAction
 * @package App\Action
 */
abstract class AbstractAction implements
    ServerMiddlewareInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;

    /**
     * Redirect to the specified route
     *
     * @param $route
     * @param array $routeParams
     * @return Response\RedirectResponse
     */
    protected function redirectToRoute($route, $routeParams = [])
    {
        return new Response\RedirectResponse(
            $this->getUrlHelper()->generate($route, $routeParams)
        );
    }
}
