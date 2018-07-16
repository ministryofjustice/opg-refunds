<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Opg\Refunds\Caseworker\DataModel\Applications\Contact as ContactDetailsModel;
use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Callback;
use Zend\Validator\ValidatorInterface;

/**
 * Class ClaimContactDetails
 * @package App\Form
 */
class ClaimContactDetails extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $notEmpty = new Validator\NotEmpty();

        // Form level input.
        $input = new Input('one-field-required');
        $input->getValidatorChain()->attach($notEmpty, true);

        $input->setRequired(true);

        $inputFilter->add($input);

        //------------------------
        // Email address field.

        $field = new Element\Email('email');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
        ;

        //---

        $input->setRequired(false);

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

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //------------------------

        $field = new Element\Textarea('address');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StripTags);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //------------------------
        // Notification opt-out

        $field = new Element\Checkbox('receive-notifications', [
            'checked_value' => 'no',
            'unchecked_value' => 'yes'
        ]);
        $input = new Input($field->getName());

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //---

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }

    public function setData($data = [])
    {
        // If at least one field is passed, enter a value into the 'one-field-required' check.
        $allFieldsEmpty = empty($data['email']) && empty($data['phone']) && empty($data['address']);
        $data['one-field-required'] = (!$allFieldsEmpty) ? 'valid' : '';

        return parent::setData($data);
    }

    /**
     * (Very) simple phone number validator
     * @return ValidatorInterface
     */
    private function getPhoneNumberValidator() : ValidatorInterface
    {
        return (new Callback(function ($value) {

            if (!preg_match('/^[+]?[0-9]+$/', $value)) {
                return false;
            }

            // Standardise number
            $number = preg_replace('/^[+]?[0]*44/', '0', $value);

            if (strlen($number) != 11) {
                return false;
            }

            // Check it's a mobile number.
            return preg_match('/^07/', $number) && !preg_match('/^070/', $number);
        }))->setMessage('phone-invalid', Callback::INVALID_VALUE);
    }

    //-----------------------------

    public function getContactDetails(): ContactDetailsModel
    {
        $data = parent::getData();

        // Filter out empty values
        $data = array_filter($data);

        $data["receive-notifications"] = (bool)($data["receive-notifications"] == "yes");

        unset($data["one-field-required"]);

        return new ContactDetailsModel($data);
    }

    public function setContactDetails(ContactDetailsModel $contactDetails)
    {
        $data = $contactDetails->getArrayCopy();

        $data["receive-notifications"] = ($data["receive-notifications"]) ? "yes" : "no";

        return $this->setData($data);
    }
}
