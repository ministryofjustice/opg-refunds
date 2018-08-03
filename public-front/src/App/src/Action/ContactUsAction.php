<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;

use Zend\Diactoros\Response\HtmlResponse;

class ContactUsAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        return new HtmlResponse($this->getTemplateRenderer()->render('app::contact-us'));
    }
}
