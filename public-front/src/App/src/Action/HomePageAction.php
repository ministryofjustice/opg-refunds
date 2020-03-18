<?php
namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\Response;

class HomePageAction extends AbstractAction
{

    private $redirectUrl;

    public function __construct(string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        return new Response\RedirectResponse($this->redirectUrl);
    }
}
