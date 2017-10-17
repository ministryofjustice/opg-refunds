<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form;
use App\Service\Refund\FlowController;

class DonorDeceasedAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        $form = new Form\DonorDeceased([
            'csrf' => $session['meta']['csrf']
        ]);

        $isUpdate = isset($session['deceased']);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $session['deceased'] = (bool)($form->getData()['donor-deceased'] === 'yes');

                // If they are deceased, return page.
                if ($session['deceased']) {
                    return new Response\HtmlResponse(
                        $this->getTemplateRenderer()->render('app::ineligible-deceased-page')
                    );
                }

                // Else pass to the flow controller.
                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            $form->setData(['donor-deceased' => ($session['deceased']) ? 'yes' : 'no' ]);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::donor-deceased', [
            'form' => $form
        ]));
    }
}
