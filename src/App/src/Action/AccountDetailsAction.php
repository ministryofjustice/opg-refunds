<?php
namespace App\Action;

use App\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Service\Refund\ProcessApplication as ProcessApplicationService;

class AccountDetailsAction extends AbstractAction
{

    private $applicationProcessService;

    public function __construct(ProcessApplicationService $applicationProcessService)
    {
        $this->applicationProcessService = $applicationProcessService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse( $this->getUrlHelper()->generate('session') );
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\AccountDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $details = $session->getArrayCopy();

                // Merge the details into the rest of the data.
                $details['account'] = [
                    'name' => $form->getData()['name'],
                    'details' => array_intersect_key($form->getData(), array_flip(['sort-code', 'account-number']))
                ];

                // Include who is applying
                $details['applicant'] = $request->getAttribute('who');

                // Process the application
                $session['reference'] = $this->applicationProcessService->process($details);

                // Remove metadata
                unset($session['meta']);

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate('apply.done', ['who' => $request->getAttribute('who')])
                );
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::account-details-page', [
            'form' => $form
        ]));
    }
}
