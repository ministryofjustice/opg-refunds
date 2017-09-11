<?php
namespace App\Action;

use App\Form;
use App\Service\Refund\FlowController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class PostcodeAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse( $this->getUrlHelper()->generate('session') );
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\Postcodes([
            'csrf' => $session['meta']['csrf']
        ]);

        $isUpdate = isset($session['postcodes']);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());
            if ($form->isValid()) {
                $session['postcodes'] = $form->getFormattedData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setData($session['postcodes']);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::postcode-page', [
            'form' => $form,
            'applicant' => $session['applicant']
        ]));
    }
}
