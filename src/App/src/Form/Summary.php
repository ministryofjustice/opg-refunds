<?php
namespace App\Form;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use Zend\Filter;

class Summary extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\Textarea('notes');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StripTags);

        $input->getValidatorChain()
            ->attach((new Validator\StringLength(['max' => 4000])));

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
    }
}
