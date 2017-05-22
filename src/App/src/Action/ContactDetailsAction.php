<?php

namespace App\Action;

use App\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use Zend\Expressive\Helper\UrlHelper;

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

                var_dump($form->getInputFilter(),$form->getData());
                die('stop');

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate('apply.summary')
                );

            } else {
                $messages = $form->getMessages();
                var_dump($messages);
                var_dump($form->getInputFilter());
                die('invalid');
            }


        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::contact-details-page', [
            'form' => $form
        ]));
    }
}
