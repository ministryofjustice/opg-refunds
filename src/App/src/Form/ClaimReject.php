<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
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
            'notInDateRange'   => 'notInDateRange',
            'noDonorLpaFound' => 'noDonorLpaFound',
            'previouslyRefunded' => 'previouslyRefunded',
            'noFeesPaid' => 'noFeesPaid',
            'claimNotVerified' => 'claimNotVerified',
            'other' => 'other',
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

        //$input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);
    }
}