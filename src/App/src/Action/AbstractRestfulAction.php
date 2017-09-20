<?php

namespace App\Action;

use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use Zend\Expressive\Router\RouteResult;

/**
 * Class AbstractRestfulAction
 * @package App\Action
 */
abstract class AbstractRestfulAction implements ServerMiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return mixed
     * @throws Exception
     */
    final public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Using the route action and the request method map to a specific action function
        $action = $request->getAttribute('action', 'index');
        $method = $request->getMethod();

        //  HTTP to CRUD mappings
        $actionMappings = [
            'index'  => RequestMethodInterface::METHOD_GET,
            'add'    => RequestMethodInterface::METHOD_POST,
            'edit'   => RequestMethodInterface::METHOD_PUT,
            'delete' => RequestMethodInterface::METHOD_DELETE,
        ];

        if (isset($actionMappings[$action]) && $actionMappings[$action] == $method) {
            return $this->{$action . 'Action'}($request, $delegate);
        }

        //  Could not match so throw an exception
        $routeResult = $request->getAttribute(RouteResult::class);
        $routeName = $routeResult->getMatchedRouteName();

        throw new Exception(sprintf('%s action is not available for route %s using %s method', $action, $routeName, $method), 404);
    }

    /**
     * READ/GET index action - override in subclass if required
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
     * CREATE/POST add action - override in subclass if required
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
     * UPDATE/PUT edit action - override in subclass if required
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
     * MODIFY/PATCH modify action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @throws Exception
     */
    public function modifyAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        throw new Exception('Modify action not implemented on ' . get_class($this), 404);
    }

    /**
     * DELETE/DELETE delete action - override in subclass if required
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
