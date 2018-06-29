<?php

namespace App\Middleware\ViewData;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as MiddlewareInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Plates\PlatesRenderer;
use Exception;

/**
 * Class ViewDataMiddleware
 * @package App\Middleware\ViewData
 */
class ViewDataMiddleware implements MiddlewareInterface
{
    /**
     * @var PlatesRenderer
     */
    private $renderer;

    /**
     * ViewDataMiddleware constructor
     *
     * @param PlatesRenderer $renderer
     */
    public function __construct(PlatesRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  Set any data that is required for all templates and layouts
        $identity = $request->getAttribute('identity');

        if ($identity instanceof User) {
            $this->renderer->addDefaultParam(PlatesRenderer::TEMPLATE_ALL, 'identity', $identity);
        }

        return $delegate->process($request);
    }
}
