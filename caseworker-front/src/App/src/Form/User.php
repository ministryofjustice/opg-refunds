<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
use Laminas\Filter;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\MultiCheckbox;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

/**
 * Form for adding and editing users
 *
 * Class User
 * @package App\Form
 */
class User extends AbstractForm
{
    /**
     * @param array $options
     * @param bool $pendingUser
     */
    public function __construct($options = [], $pendingUser = false)
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Name field
        $field = new Text('name');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter);

        $input->getValidatorChain()
              ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Email field
        $field = new Text('email');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter)
              ->attach(new Filter\StringToLower);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(), true)
            ->attach(new Validator\Email());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Roles field
        $field = new MultiCheckbox('roles');
        $input = new Input($field->getName());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $field->setValueOptions([
            UserModel::ROLE_CASEWORKER       => UserModel::ROLE_CASEWORKER,
            UserModel::ROLE_REFUND           => UserModel::ROLE_REFUND,
            UserModel::ROLE_REPORTING        => UserModel::ROLE_REPORTING,
            UserModel::ROLE_ADMIN            => UserModel::ROLE_ADMIN,
            UserModel::ROLE_QUALITY_CHECKING => UserModel::ROLE_QUALITY_CHECKING,
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //  Status field
        $field = new Radio('status');

        $field->setValueOptions([
            UserModel::STATUS_ACTIVE   => UserModel::STATUS_ACTIVE,
            UserModel::STATUS_INACTIVE => UserModel::STATUS_INACTIVE,
        ]);

        //  If the user is pending (including new users) then change the input to a hidden value
        if ($pendingUser) {
            $field = new Hidden('status');
            $field->setValue(UserModel::STATUS_PENDING);
        }

        $input = new Input($field->getName());

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
