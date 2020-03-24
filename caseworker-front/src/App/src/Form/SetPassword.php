<?php

namespace App\Form;

use App\Validator\NotEmpty;
use App\Filter\StandardInput as StandardInputFilter;
use Laminas\Form\Element\Password;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Identical;

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

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        //  Password field
        $field = new Password('password');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter());

        $input->getValidatorChain()
              ->attach(new NotEmpty(), true)
              ->attach(new Validator\Password())
              ->attach(new Identical([
                  'token' => 'confirm-password',
                  'messages' => [
                      Identical::NOT_SAME => 'password-mismatch',
                  ],
              ]));

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Confirm password field
        $field = new Password('confirm-password');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter());

        //  Only set the validation rules on the main password field to avoid duplicate messages
        //  The "Identical" validation rule will ensure that the password confirmation ends up being an acceptable value
        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
