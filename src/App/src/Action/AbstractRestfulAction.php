<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router\RouteResult;
use Exception;

/**
 * Class AbstractRestfulAction
 * @package App\Action
 */
abstract class AbstractRestfulAction extends AbstractApiClientAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    final public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Using the route action and the request method map to a specific action function
        $action = $request->getAttribute('action', 'index');

        $expectedActions = [
            'index',
            'add',
            'edit',
            'delete',
        ];

        if (!in_array($action, $expectedActions)) {
            $routeResult = $request->getAttribute(RouteResult::class);
            $routeName = $routeResult->getMatchedRouteName();

            throw new Exception(sprintf('%s action is not available for route %s', $action, $routeName), 404);
        }

        return $this->{$action . 'Action'}($request, $delegate);
    }

    /**
     * GET index action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        throw new Exception('Index action not implemented on ' . get_class($this), 404);
    }

    /**
     * GET/POST add action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        throw new Exception('Add action not implemented on ' . get_class($this), 404);
    }

    /**
     * GET/POST edit action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        throw new Exception('Edit action not implemented on ' . get_class($this), 404);
    }

    /**
     * POST delete action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @throws Exception
     */
    public function deleteAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        throw new Exception('Delete action not implemented on ' . get_class($this), 404);
    }
}
