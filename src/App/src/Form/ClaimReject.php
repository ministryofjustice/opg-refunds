<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Textarea;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class ClaimReject
 * @package App\Form
 */
class ClaimReject extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  Rejection reason
        $radioElement = new Radio('rejection-reason');
        $input = new Input($radioElement->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty);

        $radioElement->setValueOptions([
            ClaimModel::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND   => ClaimModel::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND,
            ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED => ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED,
            ClaimModel::REJECTION_REASON_NO_FEES_PAID        => ClaimModel::REJECTION_REASON_NO_FEES_PAID,
            ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED  => ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED,
        ]);

        $this->add($radioElement);
        $inputFilter->add($input);

        //  Rejection reason description field
        $field = new Textarea('rejection-reason-description');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmptyIfRadioSelected($radioElement, ['other']));

        $this->add($field);
        $inputFilter->add($input);
    }
}