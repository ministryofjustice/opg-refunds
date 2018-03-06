<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use ArrayObject;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;
use Zend\Filter\StringTrim;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Text;
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

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  System field
        $field = new Hidden('system');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Case number field
        $field = new Text('case-number');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new StringTrim);

        $caseNumberPattern = '';
        switch ($options['system']) {
            case PoaModel::SYSTEM_SIRIUS:
                $caseNumberPattern = '/^\d{4}-?\d{4}-?\d{4}$/';
                break;
            case PoaModel::SYSTEM_MERIS:
                $caseNumberPattern = '/^\d{7}\/\d{1,2}$/';
                break;
        }

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty(), true)
            ->attach(new Validator\Regex($caseNumberPattern));

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Received Date
        $receivedDate = new Fieldset\ReceivedDate();

        $this->add($receivedDate);
        $inputFilter->add($receivedDate->getInputFilter(), 'received-date');

        //  Original payment amount
        $field = new Radio('original-payment-amount');
        $input = new Input($field->getName());

        $input->setRequired(false);

        $field->setValueOptions([
            'orMore'   => 'orMore',
            'lessThan' => 'lessThan',
            'noRefund' => 'noRefund',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //  Validation

        /** @var ClaimModel $claim */
        $claim = $options['claim'];
        /** @var PoaModel $poa */
        $poa = $options['poa'];

        //  Donor checked
        $field = new Checkbox('donor-checked', [
            'checked_value' => 'yes',
            'unchecked_value' => 'no'
        ]);
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty());

        $input->setRequired(true);

        $this->add($field);
        $inputFilter->add($input);

        //  Attorney. Only present if not already verified and for backwards compatibility with older claims
        if (!$claim->isAttorneyVerified() || ($poa !== null && $poa->hasAttorneyVerification())) {
            $this->addVerificationRadio('attorney', $inputFilter);
        }

        //  Attorney name and dob. Only present if neither already verified
        if (!$claim->isAttorneyNameVerified() || !$claim->isAttorneyDobVerified()
            || ($poa !== null && ($poa->hasAttorneyNameVerification() || $poa->hasAttorneyDobVerification()))) {
            $this->addVerificationRadio('attorney-name', $inputFilter);
            $this->addVerificationRadio('attorney-dob', $inputFilter)->getValidatorChain()->attach(
                new Validator\InvalidValueCombination($this->get('attorney-dob'), $this->get('attorney-name'), [
                    'value' => 'yes',
                    'dependentValue' => 'no'
                ])
            );
        }

        //  Donor postcode. Only if supplied by claimant and not already verified
        if ($claim->getApplication()->hasDonorPostcode() &&
            (!$claim->isDonorPostcodeVerified() || ($poa !== null && $poa->hasDonorPostcodeVerification()))) {
            $this->addVerificationRadio('donor-postcode', $inputFilter);
        }

        //  Attorney postcode
        if ($claim->getApplication()->hasAttorneyPostcode() &&
            (!$claim->isAttorneyPostcodeVerified() || ($poa !== null && $poa->hasAttorneyPostcodeVerification()))) {
            $this->addVerificationRadio('attorney-postcode', $inputFilter);
        }
    }

    /**
     * @param string $inputName
     * @param InputFilter $inputFilter
     * @return Input
     */
    private function addVerificationRadio(string $inputName, InputFilter $inputFilter)
    {
        $field = new Radio($inputName);
        $input = new Input($field->getName());

        $input->setRequired(false);

        $field->setValueOptions([
            'yes' => 'yes',
            'no'  => 'no',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        return $input;
    }

    /**
     * Get form data for creating model object
     *
     * @return array
     */
    public function getModelData()
    {
        $formData = $this->getData();

        $formData['case-number'] = str_replace('-', '', $formData['case-number']);

        //  If it exists transfer the received date array into a string
        if (array_key_exists('received-date', $formData)) {
            $receivedDateDateArr = $formData['received-date'];
            $receivedDateDateStr = null;
            if (!empty($receivedDateDateArr['year']) &&
                !empty($receivedDateDateArr['month']) &&
                !empty($receivedDateDateArr['day'])) {
                $receivedDateDateStr =
                    $receivedDateDateArr['year'] . '-' .
                    sprintf('%02d', $receivedDateDateArr['month']) . '-' .
                    sprintf('%02d', $receivedDateDateArr['day']);
            }
            $formData['received-date'] = $receivedDateDateStr;
        }

        $verifications = [];
        if (array_key_exists('attorney', $formData) && !empty($formData['attorney'])) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY_NAME,
                'passes' => $formData['attorney-name'] === 'yes',
            ];
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY_DOB,
                'passes' => $formData['attorney-dob'] === 'yes',
            ];
        }
        if (array_key_exists('attorney-name', $formData) && !empty($formData['attorney-name'])) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY_NAME,
                'passes' => $formData['attorney-name'] === 'yes',
            ];
        }
        if (array_key_exists('attorney-dob', $formData) && !empty($formData['attorney-dob'])) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY_DOB,
                'passes' => $formData['attorney-dob'] === 'yes',
            ];
        }
        if (array_key_exists('donor-postcode', $formData) && !empty($formData['donor-postcode'])) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_DONOR_POSTCODE,
                'passes' => $formData['donor-postcode'] === 'yes',
            ];
        }
        if (array_key_exists('attorney-postcode', $formData) && !empty($formData['attorney-postcode'])) {
            $verifications[] = [
                'type'   => VerificationModel::TYPE_ATTORNEY_POSTCODE,
                'passes' => $formData['attorney-postcode'] === 'yes',
            ];
        }

        $formData['verifications'] = $verifications;

        return $formData;
    }

    public function bindModelData(PoaModel $poa = null)
    {
        if ($poa === null) {
            return;
        }

        $poaArray = $poa->getArrayCopy();
        unset($poaArray['id']);
        unset($poaArray['system']);
        unset($poaArray['verifications']);

        $poaArray['donor-checked'] = $poa->getId() > 0 ? 'yes' : 'no';

        $receivedDate = $poa->getReceivedDate();
        $poaArray['received-date'] = [
            'day' => $receivedDate->format('d'),
            'month' => $receivedDate->format('m'),
            'year' => $receivedDate->format('Y')
        ];

        if ($poa->getVerifications() !== null) {
            foreach ($poa->getVerifications() as $verification) {
                //Case number verification is automatic and is not displayed on the page so do not include it
                if ($verification->getType() !== VerificationModel::TYPE_CASE_NUMBER) {
                    $poaArray[$verification->getType()] = $verification->isPasses() ? 'yes' : 'no';
                }
                //Combined attorney verification has been deprecated and replaced with separate name and dob
                if ($verification->getType() === VerificationModel::TYPE_ATTORNEY) {
                    $poaArray[VerificationModel::TYPE_ATTORNEY_NAME] = $verification->isPasses() ? 'yes' : 'no';
                    $poaArray[VerificationModel::TYPE_ATTORNEY_DOB] = $verification->isPasses() ? 'yes' : 'no';
                }
            }
        }

        parent::bind(new ArrayObject($poaArray));
    }
}
