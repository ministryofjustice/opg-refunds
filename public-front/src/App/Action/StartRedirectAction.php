<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class StartRedirectAction extends AbstractAction
{

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new Response\RedirectResponse($this->getUrlHelper()->generate('eligibility.when'));
    }
}
