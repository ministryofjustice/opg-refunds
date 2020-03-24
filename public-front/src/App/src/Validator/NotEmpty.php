<?php
namespace App\Validator;

use Laminas\Validator\NotEmpty as LaminasNotEmpty;

class NotEmpty extends LaminasNotEmpty
{

    protected $messageTemplates = [
        self::IS_EMPTY => "required",
        self::INVALID  => "invalid-type",
    ];
}
