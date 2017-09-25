<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class Poa
 * @package App\Form
 */
class Poa extends AbstractForm
{
    /**
     * Poa constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Case number field
        $field = new Element\Textarea('caseNumber');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Received Date
        $receivedDate = new Fieldset\ReceivedDate();

        $this->add($receivedDate);
        $inputFilter->add($receivedDate->getInputFilter(), 'receivedDate');

        //  Original payment amount
        $field = new Element\Radio('originalPaymentAmount');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'orMore' => 'orMore',
            'lessThan' => 'lessThan',
            'noRefund' => 'noRefund',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //  Csrf field
        $this->addCsrfElement($inputFilter);
    }
}