<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Validator;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator\NotEmpty;

class AccountDetails extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);


        //------------------------
        // Name

        $field = new Element\Text('name');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim());

        $input->getValidatorChain()
            ->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Sort Code

        $field = new Element\Text('sort-code');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\PregReplace([
                'pattern'     => '/-/',
                'replacement' => '',
            ]));

        $input->getValidatorChain()
            ->attach( new NotEmpty(), true )
            ->attach( new Validator\Digits() )
            ->attach( (new Validator\StringLength(['min' => 6, 'max' => 6])) );

        $this->add($field);
        $inputFilter->add($input);

        //------------------------
        // Account Number

        $field = new Element\Text('account-number');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim());

        $input->getValidatorChain()
            ->attach( new NotEmpty(), true )
            ->attach( new Validator\Digits() )
            ->attach( (new Validator\StringLength(['min' => 8, 'max' => 8])) );

        $this->add($field);
        $inputFilter->add($input);

    }

}