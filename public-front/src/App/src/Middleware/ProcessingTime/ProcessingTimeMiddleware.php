<?php
namespace App\Middleware\ProcessingTime;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;

/**
 * Adds the expected processing time of an application into the request.
 *
 * Class ProcessingTimeMiddleware
 * @package App\Middleware\ProcessingTime
 */
class ProcessingTimeMiddleware implements ServerMiddlewareInterface
{

    private $processingTime;

    public function __construct(string $processingTime)
    {
        $this->processingTime = $processingTime;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        return $delegate->handle(
            $request->withAttribute('processingTime', $this->processingTime)
        );
    }
}
