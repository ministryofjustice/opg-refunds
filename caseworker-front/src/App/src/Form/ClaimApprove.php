<?php

namespace App\Form;

use Laminas\InputFilter\InputFilter;

/**
 * Class ClaimApprove
 * @package App\Form
 */
class ClaimApprove extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
