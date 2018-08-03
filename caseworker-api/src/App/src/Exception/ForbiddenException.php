<?php

namespace App\Exception;

use Throwable;

/**
 * Class ForbiddenException
 * @package App\Exception
 */
class ForbiddenException extends AbstractApiException
{
    /**
     * @var int
     */
    protected $code = 403;

    /**
     * NotFoundException constructor.
     *
     * @param string $message
     * @param string $title
     * @param array $additionalData
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, string $title = 'Forbidden', array $additionalData = [], Throwable $previous = null)
    {
        parent::__construct($message, $title, $additionalData, $previous);
    }
}
