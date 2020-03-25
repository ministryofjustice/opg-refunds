<?php

namespace App\Validator;

use Laminas\Validator\Regex as LaminasRegex;

/**
 * Class Regex
 * @package App\Validator
 */
class Regex extends LaminasRegex
{
    /**
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_MATCH  => 'not-match',
    ];
}
