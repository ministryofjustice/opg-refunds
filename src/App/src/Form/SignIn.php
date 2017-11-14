<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Zend\Filter;
use Zend\Form\Element\Password;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Form for signing in to the application
 *
 * Class SignIn
 * @package App\Form
 */
class SignIn extends AbstractForm
{
    /**
     * SignIn constructor
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
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Password field
        $field = new Password('password');
        $input = new Input($field->getName());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        //  TODO - Add this in the constructor if the options contain 'csrf' value
        $this->addCsrfElement($inputFilter);
    }

    /**
     * Set the authentication error reference
     */
    public function setAuthError()
    {
        $this->setMessages([
            'email' => [
                'auth-error' => 'auth-error',
            ],
        ]);
    }
}
