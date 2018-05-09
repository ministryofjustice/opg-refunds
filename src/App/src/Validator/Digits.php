<?php
namespace App\Validator;

use Zend\Validator\Digits as ZendDigits;

class Digits extends ZendDigits
{
    protected $messageTemplates = [
        self::NOT_DIGITS   => "digits-required",
        self::STRING_EMPTY => "empty-string",
        self::INVALID      => "invalid-type",
    ];
}
