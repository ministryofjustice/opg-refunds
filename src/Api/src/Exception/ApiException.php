<?php

namespace Api\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ApiException extends RuntimeException
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * ApiException constructor
     *
     * @param string $message
     * @param int $code
     * @param ResponseInterface $response
     */
    public function __construct($message, $code, ResponseInterface $response)
    {
        $this->response = $response;

        parent::__construct($message, $code);
    }

    /**
     * Returns the full response the lead to the exception.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
