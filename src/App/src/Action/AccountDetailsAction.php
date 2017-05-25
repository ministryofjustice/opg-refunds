<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Service\Refund\ProcessApplication as ProcessApplicationService;

class AccountDetailsAction implements ServerMiddlewareInterface, Initializers\TemplatingSupportInterface
{
    use Initializers\TemplatingSupportTrait;

    private $applicationProcessService;

    public function __construct(ProcessApplicationService $applicationProcessService)
    {
        $this->applicationProcessService = $applicationProcessService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::account-details-page', [

        ]));
    }

}
