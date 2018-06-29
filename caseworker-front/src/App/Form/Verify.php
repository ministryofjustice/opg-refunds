<?php

namespace App\Form;

use Zend\Form\Element\File;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\UploadFile;

/**
 * Class Verify
 * @package App\Form
 */
class Verify extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  Spreadsheet file
        $field = new File('spreadsheet');
        $input = new FileInput($field->getName());

        $input->getValidatorChain()
            ->attach(new UploadFile());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);
    }
}