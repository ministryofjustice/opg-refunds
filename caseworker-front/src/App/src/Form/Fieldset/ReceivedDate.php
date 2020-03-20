<?php

namespace App\Form\Fieldset;

use App\Filter\StandardInput as StandardInputFilter;
use App\Validator;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Callback;
use Laminas\Validator\ValidatorInterface;
use DateTime;

class ReceivedDate extends Fieldset
{
    private $inputFilter;

    public function __construct()
    {
        parent::__construct('received-date');

        $inputFilter = $this->inputFilter = new InputFilter;

        //------------------------
        // Received Date - Day

        $field = new Text('day');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(
                Validator\NotEmpty::INTEGER + Validator\NotEmpty::ZERO
            ), true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getValidDateValidator(), true)
            ->attach($this->getFutureDateValidator(), true);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Received Date - Month

        $field = new Text('month');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(
                Validator\NotEmpty::INTEGER + Validator\NotEmpty::ZERO
            ), true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getValidDateValidator(), true)
            ->attach($this->getFutureDateValidator(), true);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Received Date - Year

        $field = new Text('year');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(
                Validator\NotEmpty::INTEGER + Validator\NotEmpty::ZERO
            ), true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getValidDateValidator(), true)
            ->attach($this->getFutureDateValidator(), true);


        $this->add($field);
        $inputFilter->add($input);
    }

    //---------------------------------------

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

        $combined = [];

        foreach ($messages as $errors) {
            $combined = array_merge($combined, $errors);
        }

        return $combined;
    }

    //---------------------------------------

    private function getValidDateValidator() : ValidatorInterface
    {
        return (new Callback(function ($value, $context) {
            if (count(array_filter($context)) != 3) {
                return true;
            }
            return checkdate($context['month'], $context['day'], $context['year']) && ($context['year'] < 9999);
        }))->setMessage('invalid-date', Callback::INVALID_VALUE);
    }

    private function getFutureDateValidator() : ValidatorInterface
    {
        return (new Callback(function ($value, $context) {

            $context = array_filter($context);
            if (count($context) != 3) {
                // Don't validate unless all fields present.
                return true;
            }

            if (!checkdate($context['month'], $context['day'], $context['year'])) {
                // Don't validate if date is invalid
                return true;
            }

            $born = DateTime::createFromFormat('Y-m-d', "{$context['year']}-{$context['month']}-{$context['day']}");

            return ($born < new DateTime);
        }))->setMessage('future-date', Callback::INVALID_VALUE);
    }
}
