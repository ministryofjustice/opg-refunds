<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Service\Refund\ProcessApplication;

class SummaryAction implements ServerMiddlewareInterface, Initializers\TemplatingSupportInterface
{
    use Initializers\TemplatingSupportTrait;

    private $applicationProcessService;

    public function __construct(ProcessApplication $pa)
    {
        $this->applicationProcessService = $pa;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $session = $request->getAttribute('session');

        if( isset($request->getQueryParams()['done']) ){

            $this->applicationProcessService->process($session);

            return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::done-page', [
                
            ]));
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::summary-page', [
            'details' => $session
        ]));
    }
}
