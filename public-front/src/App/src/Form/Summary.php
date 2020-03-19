<?php
namespace App\Form;

use Laminas\Form\Element;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

use App\Validator;
use Laminas\Filter;

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
