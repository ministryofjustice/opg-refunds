<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

use App\Service\Refund\IdentFormatter;

class DoneAction implements ServerMiddlewareInterface, Initializers\TemplatingSupportInterface
{
    use Initializers\TemplatingSupportTrait;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {

        $session = $request->getAttribute('session');

        $reference = $session['reference'];

        // This will end the session.
        $session->exchangeArray([]);

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::done-page', [
            'reference' => IdentFormatter::format( $reference )
        ]));

    }
}
