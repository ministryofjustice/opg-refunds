<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Form;

class WhatFeesAction implements
    ServerMiddlewareInterface,
    Initializers\UrlHelperInterface,
    Initializers\TemplatingSupportInterface
{
    use Initializers\UrlHelperTrait;
    use Initializers\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        $form = new Form\WhatFees();

        $isUpdate = isset($session['types']);

        //var_dump([ 'what-fees' =>  $session['types'] ]); die;

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $session['types'] = $form->getData()['what-fees'];

                //var_dump($request->getParsedBody()); die;

                return new Response\RedirectResponse(
                    $this->getUrlHelper()->generate('apply.donor')
                );
            }
        } elseif ($isUpdate) {
            // We are editing previously entered details.
            $form->setData([ 'what-fees' =>  $session['types'] ]);
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::what-fees-page', [
            'form' => $form
        ]));
    }
}
