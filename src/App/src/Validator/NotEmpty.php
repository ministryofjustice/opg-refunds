<?php
namespace App\Validator;

use Zend\Validator\NotEmpty as ZendNotEmpty;

class NotEmpty extends ZendNotEmpty
{

    protected $messageTemplates = [
        self::IS_EMPTY => "required",
        self::INVALID  => "invalid-type",
    ];

}
