<?php

namespace App\Action;

use App\Form;
use App\Service\Session\Session;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class ContactDetailsAssistedDigitalAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request) || $request->getAttribute('ad') == null) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $isUpdate = isset($session['contact']);

        //---

        $form = new Form\ContactAddress([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        //---

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $session['contact'] = $form->getFormattedData();
                $session['notes'] = $form->getNotes();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session)
                    )
                );
            }

        } elseif ($isUpdate) {
            $form->setFormattedData($session['contact']);
        } else {
            // If here, pre-populate with Donor's current address.

            $address = $session['donor']['current']['address']['address-1'];

            $address.= (!empty($session['donor']['current']['address']['address-2'])) ?
                "\n".$session['donor']['current']['address']['address-2'] : '';

            $address.= (!empty($session['donor']['current']['address']['address-3'])) ?
                "\n".$session['donor']['current']['address']['address-3'] : '';

            $address.= (!empty($session['donor']['current']['address']['address-postcode'])) ?
                "\n".$session['donor']['current']['address']['address-postcode'] : '';

            $form->get('address')->setValue($address);
        }



        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-ad-page', [
            'form' => $form,
            'applicant' => $session['applicant']
        ]));
    }

}