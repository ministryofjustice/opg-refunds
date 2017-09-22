<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use Zend\Validator\Callback;
use Zend\Validator\ValidatorInterface;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

/**
 * Form for collecting the user's contact details.
 *
 * Class ContactDetails
 * @package App\Form
 */
class ContactDetails extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\MultiCheckbox('contact-options');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'email' => 'email',
            'phone' => 'phone',
        ]);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Email address field.

        $field = new Element\Email('email');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new Filter\StringToLower);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ;

        //---

        $emailValidator = new Validator\EmailAddress;

        $emailValidator->setHostnameValidator(
            new Validator\Hostname($emailValidator->getAllow())
        );

        // Special case: override the validator the field returns to allow a empty value.
        $field->setValidator(new Validator\AllowEmptyValidatorWrapper(
            $emailValidator
        ));

        //---

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Phone number field.

        $field = new Element\Tel('phone');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new Filter\PregReplace([
                // Strip out non-(alnum and +)
                'pattern'     => '/[^a-zA-Z0-9+]/',
                'replacement' => '',
            ]));

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach($this->getPhoneNumberValidator(), true);


        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
    }

    /**
     * (Very) simple phone number validator
     * @return ValidatorInterface
     */
    private function getPhoneNumberValidator() : ValidatorInterface
    {
        return (new Callback(function ($value) {
            return preg_match('/^[+]?[0-9]+$/', $value);
        }))->setMessage('phone-invalid', Callback::INVALID_VALUE);
    }
}
