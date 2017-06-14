<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;
use Fig\Http\Message\StatusCodeInterface as StatusCode;

class ScratchAction implements ServerMiddlewareInterface, Initializers\TemplatingSupportInterface
{
    use Initializers\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $index = $request->getAttribute('index');

        try {
            $template = $this->getTemplateRenderer()->render('app::'.$index);

            return new HtmlResponse($template);
        } catch (\Exception $e) {
            return new TextResponse('Template error: '.$e->getMessage(), StatusCode::STATUS_NOT_FOUND);
        }
    }
}
