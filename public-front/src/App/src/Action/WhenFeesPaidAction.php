<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use App\Form\WhenFeesPaid;

class WhenFeesPaidAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {

        $form = new WhenFeesPaid();

        $matchedRoute = $request->getAttribute('Mezzio\Router\RouteResult')->getMatchedRouteName();

        if ($matchedRoute === 'eligibility.when.answer') {
            $form->setData($request->getQueryParams());

            if ($form->isValid()) {
                if ($form->getData()['fees-in-range'] === 'yes') {
                    return new Response\RedirectResponse(
                        $this->getUrlHelper()->generate('cookies.check')
                    );
                }

                return new Response\HtmlResponse(
                    $this->getTemplateRenderer()->render('app::ineligible-timeframe-page')
                );
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::when-were-fees-paid-page', [
            'form' => $form
        ]));
    }
}
