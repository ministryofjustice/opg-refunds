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

        //---

        $this->addCsrfElement($inputFilter);
        $this->addCaseworkerNotesElement($inputFilter);
    }
}
