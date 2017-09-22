<?php

namespace App\Action;

use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

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
        $method = $request->getMethod();

        //  HTTP to CRUD mappings
        $actionMappings = [
            RequestMethodInterface::METHOD_GET    => 'index',
            RequestMethodInterface::METHOD_POST   => 'add',
            RequestMethodInterface::METHOD_PUT    => 'edit',
            RequestMethodInterface::METHOD_DELETE => 'delete',
        ];

        if (!isset($actionMappings[$method])) {
            throw new Exception(sprintf('%s method can not be used in this result action: %s', $method, get_class($this)), 404);
        }

        return $this->{$actionMappings[$method] . 'Action'}($request, $delegate);
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
