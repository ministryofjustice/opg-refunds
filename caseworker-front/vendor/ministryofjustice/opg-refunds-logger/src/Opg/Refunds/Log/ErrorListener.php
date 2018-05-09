<?php
namespace Opg\Refunds\Log;

use Throwable;

use Zend\Log\Logger;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Zend Expressive compatible error listener.
 *
 * Class ErrorListener
 * @package Opg\Refunds\Log
 */
class ErrorListener
{
    private $logger;
    private $priority;

    public function __construct( Logger $logger, int $priority )
    {
        $this->logger = $logger;
        $this->priority = $priority;
    }

    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        // Don't send notifications on 404 errors
        if ($response->getStatusCode() == 404) {
            return;
        }

        try {
            $this->logger->log($this->priority, (string)$error, [
                'status' => $response->getStatusCode(),
                'method' => $request->getMethod(),
                'url' => (string)$request->getUri(),
            ]);
        } catch (Throwable $e)
        {
        }
    }

}