<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Form\Element\Text;
use Laminas\Filter;
use Laminas\Validator\Identical;

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
        $field = new Text('email');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter())
            ->attach(new Filter\StringToLower());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(), true)
            ->attach(new Validator\Email(), true)
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
        $field = new Text('confirm-email');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter())
            ->attach(new Filter\StringToLower());

        //  Only set the validation rules on the main password field to avoid duplicate messages
        //  The "Identical" validation rule will ensure that the password confirmation ends up being an acceptable value
        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
