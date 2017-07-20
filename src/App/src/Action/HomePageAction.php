<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class HomePageAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new HtmlResponse($this->getTemplateRenderer()->render('app::home-page'));
    }
}
