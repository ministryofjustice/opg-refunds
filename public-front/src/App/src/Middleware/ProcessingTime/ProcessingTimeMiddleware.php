<?php
namespace App\Middleware\ProcessingTime;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;

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

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $delegate->process(
            $request->withAttribute('processingTime', $this->processingTime)
        );
    }
}
