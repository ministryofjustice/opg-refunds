<?php
namespace App\Action;

use App\Service\Session\Session;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class SessionFinishedAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');

        // If there's a session, end it.
        if ($session instanceof Session) {
            $session->clear();
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::session-finished-page'));
    }
}
