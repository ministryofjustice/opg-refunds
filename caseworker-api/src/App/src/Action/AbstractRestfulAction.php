<?php

namespace App\Action;

use App\Exception\NotFoundException;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractRestfulAction
 * @package App\Action
 */
abstract class AbstractRestfulAction implements RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws NotFoundException
     */
    final public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        //  Using the route action and the request method map to a specific action function
        $method = $request->getMethod();

        //  HTTP to CRUD mappings
        $actionMappings = [
            RequestMethodInterface::METHOD_GET    => 'index',
            RequestMethodInterface::METHOD_POST   => 'add',
            RequestMethodInterface::METHOD_PUT    => 'edit',
            RequestMethodInterface::METHOD_PATCH  => 'modify',
            RequestMethodInterface::METHOD_DELETE => 'delete',
        ];

        if (!isset($actionMappings[$method])) {
            throw new NotFoundException(sprintf('%s method can not be used in this result action: %s', $method, get_class($this)));
        }

        return $this->{$actionMappings[$method] . 'Action'}($request);
    }

    /**
     * READ/GET index action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws NotFoundException
     */
    public function indexAction(ServerRequestInterface $request)
    {
        throw new NotFoundException('Index action not implemented on ' . get_class($this));
    }

    /**
     * CREATE/POST add action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws NotFoundException
     */
    public function addAction(ServerRequestInterface $request)
    {
        throw new NotFoundException('Add action not implemented on ' . get_class($this));
    }

    /**
     * UPDATE/PUT edit action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws NotFoundException
     */
    public function editAction(ServerRequestInterface $request)
    {
        throw new NotFoundException('Edit action not implemented on ' . get_class($this));
    }

    /**
     * MODIFY/PATCH modify action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws NotFoundException
     */
    public function modifyAction(ServerRequestInterface $request)
    {
        throw new NotFoundException('Modify action not implemented on ' . get_class($this));
    }

    /**
     * DELETE/DELETE delete action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws NotFoundException
     */
    public function deleteAction(ServerRequestInterface $request)
    {
        throw new NotFoundException('Delete action not implemented on ' . get_class($this));
    }
}
