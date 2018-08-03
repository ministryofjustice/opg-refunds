<?php
namespace App\Action;

use App\Service\Session\Session;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Response\HtmlResponse;

class SessionFinishedAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        $session = $request->getAttribute('session');

        // If there's a session, end it.
        if ($session instanceof Session) {
            $session->clear();
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::session-finished-page'));
    }
}
