<?php
namespace App\Validator;

use Zend\Validator\StringLength as ZendStringLength;

class StringLength extends ZendStringLength
{

    protected $messageTemplates = [
        self::INVALID   => "invalid-type",
        self::TOO_SHORT => "too-short:%min%",
        self::TOO_LONG  => "too-long:%max%",
    ];
}
