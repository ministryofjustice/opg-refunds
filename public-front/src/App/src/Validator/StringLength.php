<?php
namespace App\Validator;

use Laminas\Validator\StringLength as LaminasStringLength;

class StringLength extends LaminasStringLength
{

    protected $messageTemplates = [
        self::INVALID   => "invalid-type",
        self::TOO_SHORT => "too-short:%min%",
        self::TOO_LONG  => "too-long:%max%",
    ];
}
