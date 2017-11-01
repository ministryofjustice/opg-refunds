<?php

namespace App\Form;

use App\Filter\StandardInput as StandardInputFilter;
use Zend\Form\Element\Text;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class ClaimSearch
 * @package App\Form
 */
class ClaimSearch extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  Search field
        $field = new Text('search');
        $input = new Input($field->getName());

        $input->getFilterChain()->attach(new StandardInputFilter);

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);
    }
}