<?php
namespace App\Form\Fieldset;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class Dob extends Fieldset
{

    private $inputFilter;

    public function __construct()
    {
        parent::__construct('dob');

        $inputFilter = $this->inputFilter = new InputFilter;

        //------------------------
        // DOB - Day

        $field = new Element\Text('day');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach(new Validator\Digits, true)
            ->attach(new Validator\Between(['min'=>1, 'max'=>31]), true);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Month

        $field = new Element\Text('month');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach(new Validator\Digits, true)
            ->attach(new Validator\Between(['min'=>1, 'max'=>12]), true);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Year

        $field = new Element\Text('year');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach(new Validator\Digits, true)
            ->attach(new Validator\Between(['min'=>1800, 'max'=>date('Y')]), true);

        $this->add($field);
        $inputFilter->add($input);
    }

    public function getInputFilter() : InputFilter
    {
        return $this->inputFilter;
    }

    /**
     * Combines the errors from each field into one.
     *
     * @param null $elementName
     * @return array
     */
    public function getMessages($elementName = null)
    {
        $messages = parent::getMessages($elementName);

        $combined = array();

        foreach ($messages as $errors) {
            $combined = array_merge($combined, $errors);
        }

        return $combined;
    }

}