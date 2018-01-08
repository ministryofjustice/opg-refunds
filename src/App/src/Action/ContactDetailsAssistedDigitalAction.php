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

        $isUpdate = isset($session['contact']['address']);

        //---

        $form = new Form\ContactAddress([
            'csrf' => $session['meta']['csrf']
        ]);

        //---

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $session['contact'] = $form->getFormattedData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session)
                    )
                );
            }

        } elseif ($isUpdate) {
            $form->setFormattedData($session['contact']);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-ad-page', [
            'form' => $form,
            'applicant' => $session['applicant']
        ]));
    }

}