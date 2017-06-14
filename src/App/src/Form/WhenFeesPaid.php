<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;

class WhenFeesPaid extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\Radio('fees-in-range');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'no' => 'no',
            'yes' => 'yes',
        ]);

        $this->add($field);
        $inputFilter->add($input);
    }
}
