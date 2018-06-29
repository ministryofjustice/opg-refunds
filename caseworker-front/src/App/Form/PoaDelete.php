<?php

namespace App\Form;

use Zend\InputFilter\InputFilter;

/**
 * Class PoaDelete
 * @package App\Form
 */
class PoaDelete extends AbstractForm
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