<?php

namespace App\Action;

use App\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class ContactDetailsAction implements
    ServerMiddlewareInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;


    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $form = new Form\ContactDetails();

        if ($request->getMethod() == 'POST') {
            $form->setData( $request->getParsedBody() );

            if ($form->isValid()) {
                $session = $request->getAttribute('session');

                $session['contact'] = $form->getData();

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate('apply.summary')
                );
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-page', [
            'form' => $form
        ]));
    }
}
