<?php

namespace App\Validator;

use Laminas\Validator\NotEmpty as LaminasNotEmpty;

/**
 * Class NotEmpty
 * @package App\Validator
 */
class NotEmpty extends LaminasNotEmpty
{
    /**
     * @var array
     */
    protected $messageTemplates = [
        self::IS_EMPTY => 'required',
        self::INVALID  => 'invalid-type',
    ];
}
