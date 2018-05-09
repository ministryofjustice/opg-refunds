<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class CaseNumberAction extends AbstractAction
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\CaseNumber([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        $isUpdate = isset($session['case-number']);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if (!isset($data['have-poa-case-number']) || $data['have-poa-case-number'] == 'no') {
                // Filter out the optional fields.
                $fieldsToValidate = array_flip(array_diff_key(
                    array_flip(array_keys($form->getElements() + $form->getFieldsets())),
                    // Remove the fields below from the validator.
                    array_flip(['poa-case-number'])
                ));

                $form->setValidationGroup($fieldsToValidate);
            }

            if ($form->isValid()) {
                $session['case-number'] = $form->getFormattedData();
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
            $form->setData($session['case-number']);
        } else {
            // Ensure caseworker notes are shown
            $form->setData();
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::case-number-page', [
            'form' => $form,
            'applicant' => $session['applicant'],
            'donor' => $session['donor']
        ]));
    }
}
