<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;
use Zend\Validator\Identical;

/**
 * Form for resetting a password
 *
 * Class ResetPassword
 * @package App\Form
 */
class ResetPassword extends AbstractForm
{
    /**
     * ResetPassword constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        //  Email field
        $field = new Element\Email('email');
        $input = new Input($field->getName());
        $input->setErrorMessage('invalid-email');

        $input->getFilterChain()
            ->attach(new StandardInputFilter())
            ->attach(new Filter\StringToLower());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(), true)
            ->attach(new Identical([
                'token' => 'confirm-email',
                'messages' => [
                    Identical::NOT_SAME => 'email-mismatch',
                ],
            ]));

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Confirm email field
        $field = new Element\Email('confirm-email');
        $input = new Input($field->getName());
        $input->setErrorMessage('invalid-email');

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new Filter\StringToLower());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
