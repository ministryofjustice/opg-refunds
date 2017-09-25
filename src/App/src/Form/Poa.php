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

    /**
     * Get form data for creating model object
     *
     * @return array
     */
    public function getModelData()
    {
        $formData = $this->getData();

        //  If it exists transfer the received date array into a string
        if (array_key_exists('receivedDate', $formData)) {
            $receivedDateDateArr = $formData['receivedDate'];
            $receivedDateDateStr = null;
            if (!empty($receivedDateDateArr['year']) && !empty($receivedDateDateArr['month']) && !empty($receivedDateDateArr['day'])) {
                $receivedDateDateStr = $receivedDateDateArr['year'] . '-' . $receivedDateDateArr['month'] . '-' . $receivedDateDateArr['day'];
            }
            $formData['receivedDate'] = $receivedDateDateStr;
        }

        return $formData;
    }
}