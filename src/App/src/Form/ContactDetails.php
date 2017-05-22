<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Validator;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class ContactDetails extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        //---

        $field = new Element\Tel('mobile');
        $input = new Input($field->getName());

        $input->getFilterChain()->attach(new \Zend\I18n\Filter\Alnum());

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty(0) )
            ->attach(new Validator\Digits())
            ->attach($this->getOneRequiredValidator());

        $this->add($field);
        $inputFilter->add($input);

        //---

        $field = new Element\Email('email');
        $input = new Input($field->getName());

        $input->getFilterChain()->attach(new Filter\StringToLower());

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty(0) )
            ->attach($this->getOneRequiredValidator());


        // Special case: override the validator the field returns.
        $field->setValidator(new Validator\EmailAddress());

        $this->add($field);
        $inputFilter->add($input);

        //---


    }


    public function getInputFilterSpecification()
    {
        die('happened');
    }

    private function getOneRequiredValidator()
    {
        return (new Validator\Callback(function ($value, $context) {

            //var_dump($context); die('xxx');

            // some validation
            return true;
        }))->setMessage('one-field-required', Validator\Callback::INVALID_VALUE);
    }


}