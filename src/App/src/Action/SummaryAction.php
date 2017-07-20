<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class SummaryAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            return new Response\RedirectResponse( $this->getUrlHelper()->generate('session') );
        }

        //---

        $session = $request->getAttribute('session');

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::summary-page', [
            'details' => $session,
            'who' => $request->getAttribute('who')
        ]));
    }
}
