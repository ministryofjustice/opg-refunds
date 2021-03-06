<?php
namespace App\Validator;

use Laminas\Validator\Digits as LaminasDigits;

class Digits extends LaminasDigits
{
    protected $messageTemplates = [
        self::NOT_DIGITS   => "digits-required",
        self::STRING_EMPTY => "empty-string",
        self::INVALID      => "invalid-type",
    ];
}
