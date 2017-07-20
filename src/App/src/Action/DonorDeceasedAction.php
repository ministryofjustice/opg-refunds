<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form\DonorDeceased;

class DonorDeceasedAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $form = new DonorDeceased();

        $matchedRoute = $request->getAttribute('Zend\Expressive\Router\RouteResult')->getMatchedRouteName();

        if ($matchedRoute === 'eligibility.deceased.answer') {
            $form->setData($request->getQueryParams());

            if ($form->isValid()) {
                if ($form->getData()['donor-deceased'] === 'no') {
                    return new Response\RedirectResponse(
                        $this->getUrlHelper()->generate('apply.donor', ['who' =>'attorney'])
                    );
                }

                return new Response\HtmlResponse(
                    $this->getTemplateRenderer()->render('app::ineligible-deceased-page')
                );
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::donor-deceased', [
            'form' => $form
        ]));
    }
}
