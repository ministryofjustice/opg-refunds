<?php
namespace App\Form;

use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class AccountDetails extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);


        //------------------------
        // Name

        $field = new Element\Text('name');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Sort Code

        $field = new Element\Text('sort-code');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new Filter\PregReplace([
                'pattern'     => '/-|\s/',
                'replacement' => '',
            ]));

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach(new Validator\Digits, true)
            ->attach((new Validator\StringLength(['min' => 6, 'max' => 6])));

        $this->add($field);
        $inputFilter->add($input);

        //------------------------
        // Account Number

        $field = new Element\Text('account-number');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach(new Validator\Digits, true)
            ->attach((new Validator\StringLength(['min' => 8, 'max' => 8])));

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
    }
}
