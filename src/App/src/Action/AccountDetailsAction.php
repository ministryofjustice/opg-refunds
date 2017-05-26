<?php

namespace App\Action;

use App\Form;

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

        $form = new Form\AccountDetails();

        if ($request->getMethod() == 'POST') {
            $form->setData( $request->getParsedBody() );

            if ($form->isValid()) {

                $details = $request->getAttribute('session')->getArrayCopy();

                // Merge the details into the rest of the data.
                $details['account'] = $form->getData();

                $reference = $this->applicationProcessService->process( $details );

                return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::done-page', [
                    'reference' => $reference
                ]));
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::account-details-page', [
            'form' => $form
        ]));
    }

}
