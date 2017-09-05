<?php

namespace App\Action;

use App\Service\Session\Session;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * Check if the current session is authenticated
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function authenticated(ServerRequestInterface $request)
    {
        $session = $request->getAttribute('session');

        return ($session instanceof Session && $session->loggedIn());
    }

    /**
     * Redirect to the specified route
     *
     * @param $route
     * @return Response\RedirectResponse
     */
    protected function redirectToRoute($route)
    {
        return new Response\RedirectResponse(
            $this->getUrlHelper()->generate($route)
        );
    }
}
