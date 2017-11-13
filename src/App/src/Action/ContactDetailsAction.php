<?php

namespace App\Action;

use App\Form;
use App\Service\Session\Session;
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

        //---

        $form = new Form\ContactDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        $adForm = new Form\ContactAddress([
            'csrf' => $session['meta']['csrf']
        ]);

        //---

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();

            if (isset($data['address']) && $request->getAttribute('ad') != null) {
                $adForm->setData($data);
                $result = $this->processAssistedDigitalForm($adForm, $session);
            } else {
                $form->setData($data);
                $result = $this->processStandardForm($form, $data, $session);
            }

            if (!is_null($result)) {
                return $result;
            }

        } elseif ($isUpdate) {
            if (isset($session['contact']['address'])) {
                $adForm->setData($session['contact']);
            } else {
                $form->setData($session['contact']);
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-page', [
            'form' => $form,
            'adForm' => $adForm,
            'applicant' => $session['applicant']
        ]));
    }

    private function processStandardForm(Form\ContactDetails $form, array $data, Session $session)
    {
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
                    FlowController::getNextRouteName($session)
                )
            );
        }

        return null;
    }

    private function processAssistedDigitalForm(Form\ContactAddress $form, Session $session)
    {
        if ($form->isValid()) {
            $session['contact'] = [
                'address' => $form->getData()['address']
            ];

            return new Response\RedirectResponse(
                $this->getUrlHelper()->generate(
                    FlowController::getNextRouteName($session)
                )
            );
        }

        return null;
    }
}
