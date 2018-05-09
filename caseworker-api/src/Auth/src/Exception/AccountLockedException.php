<?php

namespace Auth\Exception;

use App\Exception\AbstractApiException;
use Throwable;

/**
 * Class AccountLockedException
 * @package Auth\Exception
 */
class AccountLockedException extends AbstractApiException
{
    /**
     * @var int
     */
    protected $code = 403;

    /**
     * AccountLockedException constructor
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
