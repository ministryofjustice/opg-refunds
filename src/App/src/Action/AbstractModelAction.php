<?php

namespace App\Action;

use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router\RouteResult;
use Exception;

/**
 * Class AbstractModelAction
 * @package App\Action
 */
abstract class AbstractModelAction extends AbstractApiClientAction
{
    /**
     * @var int
     */
    protected $modelId;

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
            $actionName = 'edit';
        }

        return $this->{$actionName . 'Action'}($request, $delegate);
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
