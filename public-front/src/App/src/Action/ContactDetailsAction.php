<?php

namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;
use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\Response;

class ContactDetailsAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $isUpdate = isset($session['contact']);

        //---

        $form = new Form\ContactDetails([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        //---

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();
            $form->setData($data);

            if (isset($data['address']) && $request->getAttribute('ad') != null) {
                $form->setValidationGroup(['notes']);

                if ($form->isValid()) {
                    $session['notes'] = $form->getNotes();
                }

                //---

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate('apply.contact.address')
                );
            }

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
            // Ensure caseworker notes are shown
            $form->setData();
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-page', [
            'form' => $form,
            'applicant' => $session['applicant']
        ]));
    }
}
