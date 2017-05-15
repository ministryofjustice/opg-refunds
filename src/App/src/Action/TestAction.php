<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class TestAction implements ServerMiddlewareInterface, Templating\TemplatingSupportInterface
{
    use Templating\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new HtmlResponse($this->getTemplateRenderer()->render('app::test-page', [
            'name' => 'Bob'
        ]));
    }
}
