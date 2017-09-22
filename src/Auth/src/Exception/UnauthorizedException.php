<?php

namespace Auth\Exception;

use RuntimeException;
use Throwable;

/**
 * Class UnauthorizedException
 * @package Auth\Exception
 */
class UnauthorizedException extends RuntimeException
{
    /**
     * UnauthorizedException constructor
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Unauthorized access', Throwable $previous = null)
    {
        parent::__construct($message, 401);
    }
}
