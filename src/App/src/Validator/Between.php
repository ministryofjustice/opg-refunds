<?php
namespace App\Validator;

use Zend\Validator\Between as ZendBetween;

class Between extends ZendBetween
{

    protected $messageTemplates = [
        self::NOT_BETWEEN        => "between:%min%-%max%",
        self::NOT_BETWEEN_STRICT => "between:%min%-%max%",
    ];
}
