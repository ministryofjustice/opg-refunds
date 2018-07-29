<?php

namespace App\Action;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

/**
 * Class AbstractModelAction
 * @package App\Action
 */
abstract class AbstractModelAction extends AbstractAction
{
    /**
     * @var int
     */
    protected $modelId;

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws Exception
     */
    final public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        //  Using the route action and the request method map to a specific action function
        $method = $request->getMethod();
        $this->modelId = $request->getAttribute('id');

        //  HTTP to CRUD mappings
        $actionMappings = [
            RequestMethodInterface::METHOD_GET  => 'index',
            RequestMethodInterface::METHOD_POST => 'add',
        ];

        if (!isset($actionMappings[$method])) {
            throw new Exception(sprintf('%s method can not be used in this result action: %s', $method, get_class($this)), 404);
        }

        //  If an ID value has been provided then switch to edit mode
        $actionName = $actionMappings[$method];

        if ($method == RequestMethodInterface::METHOD_POST && !is_null($this->modelId)) {
            if (strpos($request->getUri(), '/delete')) {
                $actionName = 'delete';
            } else {
                $actionName = 'edit';
            }
        }

        return $this->{$actionName . 'Action'}($request);
    }

    /**
     * GET index action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request)
    {
        throw new Exception('Index action not implemented on ' . get_class($this), 404);
    }

    /**
     * POST add action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request)
    {
        throw new Exception('Add action not implemented on ' . get_class($this), 404);
    }

    /**
     * POST edit action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request)
    {
        throw new Exception('Edit action not implemented on ' . get_class($this), 404);
    }

    /**
     * POST delete action - override in subclass if required
     *
     * @param ServerRequestInterface $request
     * @throws Exception
     */
    public function deleteAction(ServerRequestInterface $request)
    {
        throw new Exception('Delete action not implemented on ' . get_class($this), 404);
    }
}
