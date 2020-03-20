<?php

namespace App\Form;

use Laminas\Form\Element\File;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\File\UploadFile;

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
