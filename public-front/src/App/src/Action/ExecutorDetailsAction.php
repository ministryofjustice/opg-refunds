<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class ExecutorDetailsAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\ExecutorDetails([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        $isUpdate = isset($session['executor']);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if ($form->isValid()) {
                $session['executor'] = $form->getFormattedData();
                $session['notes'] = $form->getNotes();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setFormattedData($session['executor']);
        } else {
            // Ensure caseworker notes are shown
            $form->setData();
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::executor-details-page', [
            'form' => $form,
            'who' => $request->getAttribute('who')
        ]));
    }
}
