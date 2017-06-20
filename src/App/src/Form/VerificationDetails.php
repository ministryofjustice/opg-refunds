<?php
namespace App\Form;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class VerificationDetails extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $this->addTextInput('case-number', $inputFilter);
        $this->addTextInput('donor-postcode', $inputFilter);
        $this->addTextInput('attorney-name', $inputFilter);
        $this->addTextInput('attorney-postcode', $inputFilter);

        //---

        $this->addCsrfElement($inputFilter);
    }

    private function addTextInput(string $name, InputFilter $inputFilter)
    {
        $field = new Element\Text($name);
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);

        $input->setRequired(false);
    }

    public function getFormattedData()
    {
        // Filter out empty values
        return array_filter( parent::getData() );
    }

}
