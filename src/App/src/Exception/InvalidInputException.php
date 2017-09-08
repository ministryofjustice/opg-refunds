<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

/**
 * Class InvalidInputException
 * @package App\Exception
 */
class InvalidInputException extends RuntimeException
{
    /**
     * InvalidInputException constructor
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Invalid input', Throwable $previous = null)
    {
        parent::__construct($message, 400);
    }
}
