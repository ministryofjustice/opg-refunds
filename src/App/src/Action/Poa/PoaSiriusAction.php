<?php

namespace App\Action\Poa;

use App\Action\AbstractClaimAction;
use App\Form\AbstractForm;
use App\Form\PoaSirius;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class PoaSiriusAction extends AbstractClaimAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-sirius-page', [
            'form'  => $form,
            'claim' => $this->getClaim($request)
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new PoaSirius([
            'csrf' => $session['meta']['csrf']
        ]);
        return $form;
    }
}