<?php
namespace App\Form\Fieldset;

use DateTime;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Callback;
use Zend\Validator\ValidatorInterface;

use App\Validator;
use App\Filter\Sprintf as SprintfFilter;
use App\Filter\StandardInput as StandardInputFilter;

class Dob extends Fieldset
{
    /**
     * int The minimum age, in years, that's valid.
     */
    const MIN_AGE = 18;

    /**
     * int The maximum age, in years, that's valid.
     */
    const MAX_AGE = 120;

    //---

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
            ->attach(new Validator\NotEmpty(
                Validator\NotEmpty::INTEGER + Validator\NotEmpty::ZERO
            ), true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getValidDateValidator(), true)
            ->attach($this->getFutureDateValidator(), true)
            ->attach($this->getMaxAgeValidator(), true)
            ->attach($this->getMinAgeValidator(), true);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Month

        $field = new Element\Text('month');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(
                Validator\NotEmpty::INTEGER + Validator\NotEmpty::ZERO
            ), true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getValidDateValidator(), true)
            ->attach($this->getFutureDateValidator(), true)
            ->attach($this->getMaxAgeValidator(), true)
            ->attach($this->getMinAgeValidator(), true);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Year

        $field = new Element\Text('year');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(
                Validator\NotEmpty::INTEGER + Validator\NotEmpty::ZERO
            ), true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getValidDateValidator(), true)
            ->attach($this->getFutureDateValidator(), true)
            ->attach($this->getMaxAgeValidator(), true)
            ->attach($this->getMinAgeValidator(), true);


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

        $combined = array();

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

    private function getMinAgeValidator() : ValidatorInterface
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

            // Over 18 on the 1st April 2017
            return ($born->diff(new DateTime)->y >= self::MIN_AGE);
        }))->setMessage('too-young', Callback::INVALID_VALUE);
    }

    private function getMaxAgeValidator() : ValidatorInterface
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

            // Under 120 on the 1st April 2017
            return ($born->diff(new DateTime)->y < self::MAX_AGE);
        }))->setMessage('too-old', Callback::INVALID_VALUE);
    }

}