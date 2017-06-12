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

        // Special case: override the validator the field returns to allow a empty value.
        $field->setValidator(
            new AllowEmptyValidatorWrapper(
                new Validator\EmailAddress()
            )
        );

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
            ->attach( new AllowEmptyValidatorWrapper( new Validator\Digits() ));


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