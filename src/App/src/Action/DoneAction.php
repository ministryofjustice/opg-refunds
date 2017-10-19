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
            return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::return-page'));
        }

        //---

        $session = $request->getAttribute('session');

        $contact = $session['contact'];
        $reference = $session['reference'];
        $applicant = $session['applicant'];
        $name = implode(' ', $session['donor']['current']['name']);

        // This will end the session.
        $session->clear();

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::done-page', [
            'name' => $name,
            'contact' => $contact,
            'applicant' => $applicant,
            'reference' => IdentFormatter::format($reference),
            'processingTime' => $request->getAttribute('processingTime'),
        ]));
    }
}
