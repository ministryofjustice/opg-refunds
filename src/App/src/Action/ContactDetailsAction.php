<?php

namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class ContactDetailsAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $isUpdate = isset($session['contact']);

        $form = new Form\ContactDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            $fields = array_keys($form->getElements() + $form->getFieldsets());

            // Remove these values (initially)
            $fields = array_diff($fields, ['email', 'phone']);

            $key = 'contact-options';
            if (isset($data[$key]) && is_array($data[$key]) && count($data[$key]) > 0
            ) {
                // Add back in the selected postcode fields.
                $fields = array_merge($fields, $data[$key]);
            }

            $form->setValidationGroup($fields);

            if ($form->isValid()) {
                $session['contact'] = $form->getData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setData($session['contact']);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-page', [
            'form' => $form,
            'applicant' => $session['applicant']
        ]));
    }
}
