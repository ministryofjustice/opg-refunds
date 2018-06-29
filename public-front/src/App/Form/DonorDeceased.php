<?php
namespace App\Form;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;

class DonorDeceased extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\Radio('donor-deceased');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'no' => 'no',
            'yes' => 'yes',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
        $this->addCaseworkerNotesElement($inputFilter);
    }
}
