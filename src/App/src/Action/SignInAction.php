<?php

namespace App\Action;

use App\Form\SignIn;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class SignInAction extends AbstractAction
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  If the user is authenticated then redirect to the home page
        if ($this->authenticated($request)) {
            return $this->redirectToRoute('home');
        }

        $session = $request->getAttribute('session');

        $form = new SignIn([
            'csrf' => $session['meta']['csrf']
        ]);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                //  TODO - Get the identity properly with auth service - for now just populate empty standard class
                $session->setIdentity(new \stdClass());

                return $this->redirectToRoute('home');
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::sign-in-page', [
            'form' => $form,
        ]));
    }
}
