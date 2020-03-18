<?php
namespace App\Form;

use Laminas\Form\Element;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

use App\Validator;

class AboutYou extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\Radio('who');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'donor' => 'donor',
            'attorney' => 'attorney',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
        $this->addCaseworkerNotesElement($inputFilter);
    }
}
