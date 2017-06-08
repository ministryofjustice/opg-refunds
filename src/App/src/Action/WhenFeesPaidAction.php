<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form\WhenFeesPaid;

class WhenFeesPaidAction implements ServerMiddlewareInterface, Initializers\TemplatingSupportInterface
{
    use Initializers\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $form = new WhenFeesPaid();

        $matchedRoute = $request->getAttribute('Zend\Expressive\Router\RouteResult')->getMatchedRouteName();

        if ($matchedRoute === 'eligibility.when.answer') {
            $form->setData( $request->getQueryParams() );

            if ($form->isValid()) {
                die('moving on');
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::when-were-fees-paid', [
            'form' => $form
        ]));
    }
}
