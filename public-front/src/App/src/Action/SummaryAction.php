<?php
namespace App\Action;

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

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        if (!$this->isActionAccessible($request)) {
            if (isset($request->getCookieParams()['complete'])) {
                return new Response\RedirectResponse($this->getUrlHelper()->generate('apply.done'));
            }

            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\Summary([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        //---

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                // Add AD details if we have some.
                if (($ad = $request->getAttribute('ad')) != null) {
                    $session['ad'] = [
                        'meta' => $ad,
                        'notes' => $form->getNotes()
                    ];
                }

                // Add the date a respond will be expected.
                $session['expected'] = date('Y-m-d', strtotime($request->getAttribute('processingTime')));

                // Process the application
                $session['reference'] = $this->applicationProcessService->process($session->getArrayCopy());

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } else {
            // Ensure caseworker notes are shown
            $form->setData();
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
