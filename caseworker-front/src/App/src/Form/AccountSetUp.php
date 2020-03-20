<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

/**
 * Form to re-request the user account set up
 *
 * Class AccountSetUp
 * @package App\Form
 */
class AccountSetUp extends AbstractForm
{
    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  User ID field
        $field = new Hidden('id');
        $input = new Input($field->getName());

        $input->getFilterChain()
              ->attach(new StandardInputFilter);

        $input->getValidatorChain()
              ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}
