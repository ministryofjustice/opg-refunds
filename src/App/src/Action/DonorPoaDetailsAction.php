<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class DonorPoaDetailsAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\DonorPoaDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        $isUpdate = isset($session['donor']['poa']);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            // If they have not checked to enter a second name, don't validate those fields.
            if (isset($data['different-name-on-poa']) && $data['different-name-on-poa'] === 'no') {
                // Filter out the optional fields.
                $fieldsToValidate = array_flip(array_diff_key(
                    array_flip(array_keys($form->getElements() + $form->getFieldsets())),
                    // Remove the fields below from the validator.
                    array_flip(['title', 'first', 'last'])
                ));

                $form->setValidationGroup($fieldsToValidate);
            }

            if ($form->isValid()) {
                if (!isset($session['donor'])) {
                    $session['donor'] = [];
                }

                $session['donor']['poa'] = $form->getFormattedData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setFormattedData($session['donor']['poa']);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::donor-poa-details-page', [
            'form' => $form,
            'who' => $request->getAttribute('who'),
            'applicant' => $session['applicant'],
            'name' => $session['donor']['current']['name']
        ]));
    }
}
