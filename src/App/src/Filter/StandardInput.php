<?php

namespace App\Filter;

use Zend\Filter;

/**
 * Standard filters that should be applied to most text inputs
 *
 * Class StandardInput
 * @package App\Filter
 */
class StandardInput extends Filter\FilterChain
{

    public function __construct($options = null)
    {
        parent::__construct($options);

        $this->attach(new Filter\StringTrim());
        $this->attach(new Filter\StripTags());
        $this->attach(new Filter\StripNewlines());
    }
}
