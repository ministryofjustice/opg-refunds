<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class ClaimAccept
 * @package App\Form
 */
class ClaimAccept extends AbstractForm
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