<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class ContactDetails extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        //---

        $this->add(
            new Element\Tel('mobile')
        );

        $inputFilter->add(
            new Input('mobile')
        );

        //---

        $this->add(
            new Element\Email('email')
        );

        $inputFilter->add(
            new Input('email')
        );
    }


}