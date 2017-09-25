<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
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
     * @var string
     */
    protected $system;

    /**
     * Poa constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

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
            'orMore'   => 'orMore',
            'lessThan' => 'lessThan',
            'noRefund' => 'noRefund',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //  Validation
        //  Attorney details (always present)
        $field = new Element\Radio('attorney');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'yes' => 'yes',
            'no'  => 'no',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        if (isset($options['claim'])) {
            /** @var ClaimModel $claim */
            $claim = $options['claim'];

            //  Donor postcode
            if ($claim->getApplication()->getPostcodes()->getDonorPostcode() !== null) {
                $field = new Element\Radio('donor-postcode');
                $input = new Input($field->getName());

                $input->getValidatorChain()->attach(new Validator\NotEmpty);

                $field->setValueOptions([
                    'yes' => 'yes',
                    'no' => 'no',
                ]);

                $this->add($field);
                $inputFilter->add($input);
            }

            //  Donor postcode
            if ($claim->getApplication()->getPostcodes()->getAttorneyPostcode() !== null) {
                $field = new Element\Radio('attorney-postcode');
                $input = new Input($field->getName());

                $input->getValidatorChain()->attach(new Validator\NotEmpty);

                $field->setValueOptions([
                    'yes' => 'yes',
                    'no' => 'no',
                ]);

                $this->add($field);
                $inputFilter->add($input);
            }
        }
    }

    /**
     * Get form data for creating model object
     *
     * @return array
     */
    public function getModelData()
    {
        $formData = $this->getData();

        $formData['system'] = $this->system;

        //  If it exists transfer the received date array into a string
        if (array_key_exists('receivedDate', $formData)) {
            $receivedDateDateArr = $formData['receivedDate'];
            $receivedDateDateStr = null;
            if (!empty($receivedDateDateArr['year']) && !empty($receivedDateDateArr['month']) && !empty($receivedDateDateArr['day'])) {
                $receivedDateDateStr = $receivedDateDateArr['year'] . '-' . $receivedDateDateArr['month'] . '-' . $receivedDateDateArr['day'];
            }
            $formData['receivedDate'] = $receivedDateDateStr;
        }

        $verifications = [];
        if (array_key_exists('attorney', $formData)) {
            $verifications[] = [
                'type'   => 'attorney',
                'passes' => $formData['attorney'] === 'yes',
            ];
        }
        if (array_key_exists('donor-postcode', $formData)) {
            $verifications[] = [
                'type'   => 'donor-postcode',
                'passes' => $formData['donor-postcode'] === 'yes',
            ];
        }
        if (array_key_exists('attorney-postcode', $formData)) {
            $verifications[] = [
                'type'   => 'attorney-postcode',
                'passes' => $formData['attorney-postcode'] === 'yes',
            ];
        }

        $formData['verifications'] = $verifications;

        return $formData;
    }
}