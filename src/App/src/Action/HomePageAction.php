<?php

namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class HomePageAction extends AbstractAction
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //  If the user isn't logged in then redirect to login screen
        if (!$this->authenticated($request)) {
            return $this->redirectToRoute('sign.in');
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page'));
    }
}
