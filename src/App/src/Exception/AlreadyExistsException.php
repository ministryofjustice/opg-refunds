<?php

namespace App\Exception;

use RuntimeException;
use Throwable;

/**
 * Class AlreadyExistsException
 * @package App\Exception
 */
class AlreadyExistsException extends RuntimeException
{
    /**
     * AlreadyExistsException constructor
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Entity already exists', Throwable $previous = null)
    {
        parent::__construct($message, 409);
    }
}
