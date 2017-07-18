<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form\AboutYou;

class WhoAction implements
    ServerMiddlewareInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $form = new AboutYou();

        $matchedRoute = $request->getAttribute('Zend\Expressive\Router\RouteResult')->getMatchedRouteName();

        if ($matchedRoute === 'eligibility.who.answer') {
            $form->setData($request->getQueryParams());

            if ($form->isValid()) {
                if ($form->getData()['who'] === 'donor') {
                    return new Response\RedirectResponse(
                        $this->getUrlHelper()->generate('apply.donor', ['who' =>'donor'])
                    );
                }

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate('eligibility.deceased')
                );
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::who-page', [
            'form' => $form
        ]));
    }
}
