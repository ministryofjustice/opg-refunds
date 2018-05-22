<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form;
use App\Service\Refund\FlowController;

class WhoAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        if ($request->getAttribute('isDonorDeceased') == true) {
            //This is a donor deceased phone claim. The claimant must be the executor so set them to that and redirect
            //They should not be allowed to change this option so none of the further checks are required
            $session['applicant'] = 'executor';

            return new Response\RedirectResponse(
                $this->getUrlHelper()->generate(
                    FlowController::getNextRouteName($session),
                    ['who'=>$session['applicant']]
                )
            );
        }

        $form = new Form\AboutYou([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        $isUpdate = isset($session['applicant']);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $applicant = $form->getData()['who'];

                if ($isUpdate && $applicant != $session['applicant']) {
                    // Remove all session data if the applicant changes.
                    $session->clear();
                } elseif (isset($session['deceased'])) {
                    // Always require them to (re-)confirm if they're deceased
                    unset($session['deceased']);
                }

                $session['applicant'] = $applicant;
                $session['notes'] = $form->getNotes();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            } elseif (isset($form->getMessages()['secret'])) {
                // Special case - a CSRF error here is most likely an undetected session expiry.
                return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
            }
        } elseif ($isUpdate) {
            $form->setData(['who' => $session['applicant']]);
        } else {
            // Ensure caseworker notes are shown
            $form->setData();
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::who-page', [
            'form' => $form
        ]));
    }
}
