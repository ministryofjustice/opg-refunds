<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Service\Refund\IdentFormatter;

class DoneAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (!$this->isActionAccessible($request)) {
            die('cannot access action');
        }

        //---

        $session = $request->getAttribute('session');

        $reference = $session['reference'];

        // This will end the session.
        $session->exchangeArray([]);

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::done-page', [
            'reference' => IdentFormatter::format($reference)
        ]));
    }
}
