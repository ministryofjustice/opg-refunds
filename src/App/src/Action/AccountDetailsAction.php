<?php
namespace App\Action;

use App\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Service\Refund\FlowController;
use App\Service\Refund\Data\BankDetailsHandler;

class AccountDetailsAction extends AbstractAction
{

    private $bankDetailsHandlerService;

    public function __construct(BankDetailsHandler $bankDetailsHandlerService)
    {
        $this->bankDetailsHandlerService = $bankDetailsHandlerService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\AccountDetails([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $data = $request->getParsedBody();

            //---------------------
            // Check for cheque

            if (isset($data['cheque']) && $request->getAttribute('ad') != null) {
                $session['cheque'] = true;

                if (isset($session['account'])) {
                    unset($session['account']);
                }
                
                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session))
                );
            }

            //---------------------

            $form->setData($data);

            if ($form->isValid()) {
                // Prep the account before storage.
                $session['account'] = $this->bankDetailsHandlerService->process($form->getData());

                $session['cheque'] = false;

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session))
                );
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::account-details-page', [
            'form' => $form,
            'applicant' => $session['applicant']
        ]));
    }
}
