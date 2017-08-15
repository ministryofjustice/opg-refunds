<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form;
use App\Service\Refund\FlowController;
use App\Service\Refund\ProcessApplication as ProcessApplicationService;

class SummaryAction extends AbstractAction
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

        $form = new Form\Csrf([
            'csrf' => $session['meta']['csrf']
        ]);

        //---

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                // Add the date a respond will be expected.
                $session['expected'] = date('Y-m-d', strtotime($request->getAttribute('processingTime')));

                // Process the application
                $session['reference'] = $this->applicationProcessService->process($session->getArrayCopy());

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$request->getAttribute('who')]
                    )
                );
            }
        }

        //---

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::summary-page', [
            'details' => $session,
            'form' => $form,
            'who' => $request->getAttribute('who'),
            'applicant' => $session['applicant']
        ]));
    }
}
