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

        $notEmpty = new Validator\NotEmpty();
        $notEmpty->setMessage('one-field-required', Validator\NotEmpty::IS_EMPTY);

        // Form level input.
        $input = new Input('one-field-required');
        $input->getValidatorChain()->attach($notEmpty, true);
        $input->setRequired(true);
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
            ->attach((new Validator\StringLength(['max' => 30])));

        $this->add($field);
        $inputFilter->add($input);

        $input->setRequired(false);
    }

    public function setData($data)
    {
        // If at least one field is passed, enter a value into the 'one-field-required' check.
        $allFieldsEmpty = empty($data['donor-postcode']) && empty($data['attorney-postcode']);
        $data['one-field-required'] = (!$allFieldsEmpty) ? 'valid' : '';

        return parent::setData($data);
    }

    public function getFormattedData()
    {
        $data = parent::getData();

        unset($data['one-field-required']);

        // Filter out empty values
        return array_filter( $data );
    }

}
