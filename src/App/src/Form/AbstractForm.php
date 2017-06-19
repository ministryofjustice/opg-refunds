<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;

class AbstractForm extends ZendForm
{

    protected function addCsrfElement(InputFilter $inputFilter)
    {
        $options = $this->getOptions();

        $field = new Element\Csrf('secret');
        $input = new Input($field->getName());

        $field->setCsrfValidator(new Validator\Csrf([
            'name' => $this->getName(),
            'secret' => $options['csrf']
        ]));

        $input->getValidatorChain()->attach(new Validator\NotEmpty());

        $this->add($field);
        $inputFilter->add($input);
    }

}
