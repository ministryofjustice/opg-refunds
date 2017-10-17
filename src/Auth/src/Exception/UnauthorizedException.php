<?php

namespace Auth\Exception;

use App\Exception\AbstractApiException;
use Throwable;

/**
 * Class UnauthorizedException
 * @package Auth\Exception
 */
class UnauthorizedException extends AbstractApiException
{
    /**
     * @var int
     */
    protected $code = 401;

    /**
     * @var string
     */
    protected $type = 'What should this be?';

    /**
     * AlreadyExistsException constructor
     *
     * @param string $message
     * @param string $title
     * @param array $additionalData
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, string $title = 'Unauthorized access', array $additionalData = [], Throwable $previous = null)
    {
        parent::__construct($message, $title, $additionalData, $previous);
    }
}
