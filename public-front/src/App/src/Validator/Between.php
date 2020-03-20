<?php
namespace App\Validator;

use Laminas\Validator\Between as LaminasBetween;

class Between extends LaminasBetween
{

    protected $messageTemplates = [
        self::NOT_BETWEEN        => "between:%min%-%max%",
        self::NOT_BETWEEN_STRICT => "between:%min%-%max%",
    ];
}
