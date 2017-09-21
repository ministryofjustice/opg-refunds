<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Zend\Filter;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Form for adding and editing caseworkers
 *
 * Class Caseworker
 * @package App\Form
 */
class Caseworker extends AbstractForm
{
    /**
     * SignIn constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Name field
        $field = new Element\Text('name');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter);

        $input->getValidatorChain()
              ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Email field
        $field = new Element\Email('email');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter)
              ->attach(new Filter\StringToLower);

        $input->getValidatorChain()
              ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);




        //  Status field
        //TODO

        //  Roles field
        //TODO




        //  Csrf field
        //  TODO - Add this in the constructor if the options contain 'csrf' value
        $this->addCsrfElement($inputFilter);
    }
}
