<?php
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class HomePageAction extends AbstractAction
{

    private $redirectUrl;

    public function __construct(string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return new Response\RedirectResponse($this->redirectUrl);
    }
}
