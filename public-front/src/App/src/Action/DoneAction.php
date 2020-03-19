<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\FigResponseCookies;

use App\Service\Refund\IdentFormatter;

class DoneAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
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

        //---

        $response = new Response\HtmlResponse($this->getTemplateRenderer()->render('app::done-page', [
            'name' => $name,
            'contact' => $contact,
            'applicant' => $applicant,
            'reference' => IdentFormatter::format($reference),
            'processingTime' => $request->getAttribute('processingTime'),
        ]));

        //---

        $response = FigResponseCookies::set($response, SetCookie::create('complete')
            ->withValue("yes")
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withPath('/')
            ->withExpires(new \DateTime("+1 day")));

        //---

        return $response;
    }
}
