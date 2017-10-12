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

        $form = new Form\AboutYou([
            'csrf' => $session['meta']['csrf']
        ]);

        $isUpdate = isset($session['applicant']);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $applicant = $form->getData()['who'];

                if ($isUpdate && $applicant != $session['applicant']) {
                    // Remove all session data if the applicant changes.
                    $session->exchangeArray([]);
                } elseif (isset($session['deceased'])) {
                    // Always require them to (re-)confirm if they're deceased
                    unset($session['deceased']);
                }

                $session['applicant'] = $applicant;

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            $form->setData(['who' => $session['applicant']]);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::who-page', [
            'form' => $form
        ]));
    }
}
