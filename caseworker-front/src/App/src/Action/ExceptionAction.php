<?php
namespace App\Action;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request) : \Psr\Http\Message\ResponseInterface
    {
        throw new \Exception('Exception Test');
    }
}
