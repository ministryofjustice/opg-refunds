<?php
namespace App\Form\Fieldset;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Callback;
use Zend\Validator\ValidatorInterface;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class Dob extends Fieldset
{
    /**
     * int The maximum age, in years, that's valid.
     */
    const MAX_AGE = 150;

    private $inputFilter;

    public function __construct(bool $canBeEmpty = false)
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
            ->attach(new Validator\NotEmpty( ($canBeEmpty) ? 0 : null ), true)
            ->attach(new Validator\AllowEmptyValidatorWrapper(new Validator\Digits), true)
            ->attach(new Validator\AllowEmptyValidatorWrapper(
                new Validator\Between(['min'=>1, 'max'=>31])
            ), true)
            ->attach($this->getValidDateValidator(), true);

        if ($canBeEmpty) {
            $input->getValidatorChain()
                ->attach($this->getAllOrNoneValidator(), true);
        }

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Month

        $field = new Element\Text('month');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty( ($canBeEmpty) ? 0 : null ), true)
            ->attach(new Validator\AllowEmptyValidatorWrapper(new Validator\Digits), true)
            ->attach(new Validator\AllowEmptyValidatorWrapper(
                new Validator\Between(['min'=>1, 'max'=>12])
            ), true);

        if ($canBeEmpty) {
            $input->getValidatorChain()
                ->attach($this->getAllOrNoneValidator(), true);
        }

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Year

        $field = new Element\Text('year');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $year = (int)date('Y');

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty( ($canBeEmpty) ? 0 : null ), true)
            ->attach(new Validator\AllowEmptyValidatorWrapper(new Validator\Digits), true)
            ->attach(new Validator\AllowEmptyValidatorWrapper(
                new Validator\Between(['min'=>($year-self::MAX_AGE), 'max'=>$year])
            ), true);

        if ($canBeEmpty) {
            $input->getValidatorChain()
                ->attach($this->getAllOrNoneValidator(), true);
        }

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

    /**
     * Validator is true if all DOB fields are supplied, or is none are.
     *
     * @return ValidatorInterface
     */
    public function getAllOrNoneValidator() : ValidatorInterface
    {
        return (new Callback(function ($value, $context) {
            $context = array_filter($context);
            return in_array(count($context), [0, 3]);
        }))->setMessage('all-or-none-fields-required', Callback::INVALID_VALUE);
    }

    public function getValidDateValidator() : ValidatorInterface
    {
        return (new Callback(function ($value, $context) {
            $context = array_filter($context);
            return (count($context) != 3) || checkdate($context['month'], $context['day'], $context['year']);
        }))->setMessage('invalid-date', Callback::INVALID_VALUE);
    }

}