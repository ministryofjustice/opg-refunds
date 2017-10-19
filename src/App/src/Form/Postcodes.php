<?php
namespace App\Form;

use Zend\Filter;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class Postcodes extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\MultiCheckbox('postcode-options');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'donor-postcode' => 'donor-postcode',
            'attorney-postcode' => 'attorney-postcode',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //------------------------

        $this->addTextInput('donor-postcode', $inputFilter);
        $this->addTextInput('attorney-postcode', $inputFilter);

        //---

        $this->addCsrfElement($inputFilter);
    }

    private function addTextInput(string $name, InputFilter $inputFilter)
    {
        $field = new Element\Text($name);
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new Filter\StringToUpper);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 20])));

        $this->add($field);
        $inputFilter->add($input);
    }

    public function getFormattedData()
    {
        $data = parent::getData();

        // Filter out empty values
        return array_filter($data);
    }
}
