<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Validator;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator\NotEmpty;
use App\Validator\AllowEmptyValidatorWrapper;

/**
 * Form for collecting the user's contact details.
 *
 * Class ContactDetails
 * @package App\Form
 */
class ContactDetails extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        //------------------------
        // Email address field.

        $field = new Element\Email('email');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StringToLower());

        $input->getValidatorChain()
            ->attach( new NotEmpty(0) )
            ->attach($this->getOneRequiredValidator(), true, 100)
            ;

        //---

        $emailInvalidMessage = 'email-invalid';
        
        // Special case: override the validator the field returns to allow a empty value.
        $field->setValidator(
            new AllowEmptyValidatorWrapper(
                new Validator\EmailAddress([
                    'setMessages' => [
                        Validator\EmailAddress::INVALID            => $emailInvalidMessage,
                        Validator\EmailAddress::INVALID_FORMAT     => $emailInvalidMessage,
                        Validator\EmailAddress::INVALID_HOSTNAME   => $emailInvalidMessage,
                        Validator\EmailAddress::INVALID_MX_RECORD  => $emailInvalidMessage,
                        Validator\EmailAddress::INVALID_SEGMENT    => $emailInvalidMessage,
                        Validator\EmailAddress::DOT_ATOM           => $emailInvalidMessage,
                        Validator\EmailAddress::QUOTED_STRING      => $emailInvalidMessage,
                        Validator\EmailAddress::INVALID_LOCAL_PART => $emailInvalidMessage,
                        Validator\EmailAddress::LENGTH_EXCEEDED    => $emailInvalidMessage
                    ]
                ])
            )
        );

        //---

        $input->setRequired(true);
        $input->setContinueIfEmpty(true);

        $this->add($field);
        $inputFilter->add($input);

        //------------------------
        // Mobile number field.

        $field = new Element\Tel('mobile');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new \Zend\I18n\Filter\Alnum());

        $input->getValidatorChain()
            ->attach( new NotEmpty(0) )
            ->attach( new AllowEmptyValidatorWrapper( new Validator\Digits([
                'setMessages' => [ Validator\Digits::NOT_DIGITS => 'digits-required' ]
            ])));


        $input->setRequired(true);
        $input->setContinueIfEmpty(true);

        $this->add($field);
        $inputFilter->add($input);

        //---
    }

    /**
     * Returns a validator for checking that either email or mobile is completed.
     *
     * @return Validator\ValidatorInterface
     */
    private function getOneRequiredValidator() : Validator\ValidatorInterface
    {
        return (new Validator\Callback(function ($value, $context) {
            return !empty($context['email']) || !empty($context['mobile']);
        }
        ))->setMessage('one-field-required', Validator\Callback::INVALID_VALUE);
    }


}