<?php

namespace App\Validator;

class ClaimId extends Regex
{
    /**
     * Email constructor
     */
    public function __construct()
    {
        $this->messageTemplates[self::NOT_MATCH] = 'invalid-claim-code';

        parent::__construct('/^(R)?\d{0,3}( )?\d{0,4}( )?\d{1,4}$/');
    }
}