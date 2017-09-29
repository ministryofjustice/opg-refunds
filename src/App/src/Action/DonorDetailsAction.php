<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class DonorDetailsAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        // Include who is applying
        $session['applicant'] = $request->getAttribute('who');

        //---

        $form = new Form\DonorCurrentDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        $isUpdate = isset($session['donor']);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if ($form->isValid()) {
                if (!isset($session['donor'])) {
                    $session['donor'] = [];
                }

                $session['donor']['current'] = $form->getFormattedData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setFormattedData($session['donor']);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::donor-details-page', [
            'form' => $form,
            'who' => $request->getAttribute('who'),
            'applicant' => $session['applicant']
        ]));
    }
}
