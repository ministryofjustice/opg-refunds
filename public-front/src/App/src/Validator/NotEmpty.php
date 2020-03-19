<?php
namespace App\Validator;

use Laminas\Validator\NotEmpty as ZendNotEmpty;

class NotEmpty extends ZendNotEmpty
{

    protected $messageTemplates = [
        self::IS_EMPTY => "required",
        self::INVALID  => "invalid-type",
    ];
}
