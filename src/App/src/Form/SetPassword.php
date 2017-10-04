<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Zend\Filter;
use Zend\Form\Element\Password;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;

/**
 * Form for setting a password
 *
 * Class SetPassword
 * @package App\Form
 */
class SetPassword extends AbstractForm
{
    /**
     * SetPassword constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Password field
        $field = new Password('password');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter);

        $identicalValidator = new Identical('confirm-password');

        $input->getValidatorChain()
              ->attach(new Validator\NotEmpty())
              ->attach($identicalValidator);

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Confirm password field
        $field = new Password('confirm-password');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter)
              ->attach(new Filter\StringToLower);

        $input->getValidatorChain()
              ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
