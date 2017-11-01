<?php

namespace App\Exception;

use Throwable;

/**
 * Class AlreadyExistsException
 * @package App\Exception
 */
class AlreadyExistsException extends AbstractApiException
{
    /**
     * @var int
     */
    protected $code = 409;

    /**
     * AlreadyExistsException constructor
     *
     * @param string $message
     * @param string $title
     * @param array $additionalData
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, string $title = 'Entity already exists', array $additionalData = [], Throwable $previous = null)
    {
        parent::__construct($message, $title, $additionalData, $previous);
    }
}
