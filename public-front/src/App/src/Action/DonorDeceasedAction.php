<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form;
use App\Service\Refund\FlowController;

class DonorDeceasedAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $matchedRoute = $request->getAttribute('Zend\Expressive\Router\RouteResult')->getMatchedRouteName();

        if ($matchedRoute === 'eligibility.donor.deceased') {
            return new Response\HtmlResponse(
                $this->getTemplateRenderer()->render('app::ineligible-deceased-page')
            );
        }

        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse($this->getUrlHelper()->generate('session'));
        }

        //---

        $session = $request->getAttribute('session');

        $form = new Form\DonorDeceased([
            'csrf' => $session['meta']['csrf'],
            'notes' => ($session['notes']) ?? null,
        ]);

        $isUpdate = isset($session['deceased']);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $session['deceased'] = (bool)($form->getData()['donor-deceased'] === 'yes');
                $session['notes'] = $form->getNotes();

                // If they are deceased, and it's not a Donor Deceased AD session, return page.
                $ad = $request->getAttribute('ad');
                if ($session['deceased'] && $request->getAttribute('isDonorDeceased') == false) {
                    return new Response\RedirectResponse(
                        $this->getUrlHelper()->generate('eligibility.donor.deceased')
                    );
                }

                // Else pass to the flow controller.
                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate(
                        FlowController::getNextRouteName($session),
                        ['who'=>$session['applicant']]
                    )
                );
            }
        } elseif ($isUpdate) {
            $form->setData(['donor-deceased' => ($session['deceased']) ? 'yes' : 'no' ]);
        } else {
            // Ensure caseworker notes are shown
            $form->setData();
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::donor-deceased', [
            'form' => $form
        ]));
    }
}
