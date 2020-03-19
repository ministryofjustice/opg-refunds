<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;

class StartRedirectAction extends AbstractAction
{

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        return new Response\RedirectResponse($this->getUrlHelper()->generate('eligibility.when'));
    }
}
