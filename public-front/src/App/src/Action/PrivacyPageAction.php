<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\Response\HtmlResponse;

class PrivacyPageAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        return new HtmlResponse($this->getTemplateRenderer()->render('app::privacy-page'));
    }
}
