<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
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
     * @param array $queryParams
     * @return Response\RedirectResponse
     */
    protected function redirectToRoute($route, $routeParams = [], $queryParams = [])
    {
        return new Response\RedirectResponse(
            $this->getUrlHelper()->generate($route, $routeParams, $queryParams)
        );
    }

    protected function setFlashInfoMessage(ServerRequestInterface $request, $message)
    {
        $this->setFlashMessage($request, 'info', $message);
    }

    protected function setFlashMessage(ServerRequestInterface $request, $key, $message)
    {
        /** @var Messages $flash */
        $flash = $request->getAttribute('flash');
        $flash->addMessage($key, $message);
    }

    protected function getFlashMessages(ServerRequestInterface $request)
    {
        /** @var Messages $flash */
        $flash = $request->getAttribute('flash');
        return $flash->getMessages();
    }
}
