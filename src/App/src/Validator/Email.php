<?php

namespace App\Validator;

/**
 * Class Email
 * @package App\Validator
 */
class Email extends Regex
{
    /**
     * Email constructor
     */
    public function __construct()
    {
        $this->messageTemplates[self::NOT_MATCH] = 'invalid-email';

        parent::__construct('/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@(publicguardian.gsi.gov.uk|digital.justice.gov.uk)/');
    }
}
