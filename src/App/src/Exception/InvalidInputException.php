<?php

namespace App\Exception;

use Throwable;

/**
 * Class InvalidInputException
 * @package App\Exception
 */
class InvalidInputException extends AbstractApiException
{
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * InvalidInputException constructor
     *
     * @param string $message
     * @param string $title
     * @param array $additionalData
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, string $title = 'Invalid input', array $additionalData = [], Throwable $previous = null)
    {
        parent::__construct($message, $title, $additionalData, $previous);
    }
}
