<?php

namespace App\Form;

use App\Filter\StandardInput as StandardInputFilter;
use App\Validator;
use Opg\Refunds\Caseworker\DataModel\Applications\AssistedDigital as AssistedDigitalModel;
use Zend\Form\Element\Radio;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class PhoneClaim
 * @package App\Form
 */
class PhoneClaim extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  Type selection
        $field = new Radio('type');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty);

        $input->setRequired(true);

        $field->setValueOptions([
            AssistedDigitalModel::TYPE_DONOR_DECEASED      => AssistedDigitalModel::TYPE_DONOR_DECEASED,
            AssistedDigitalModel::TYPE_ASSISTED_DIGITAL    => AssistedDigitalModel::TYPE_ASSISTED_DIGITAL,
            AssistedDigitalModel::TYPE_REFUSE_CLAIM_ONLINE => AssistedDigitalModel::TYPE_REFUSE_CLAIM_ONLINE,
            AssistedDigitalModel::TYPE_DEPUTY              => AssistedDigitalModel::TYPE_DEPUTY,
            AssistedDigitalModel::TYPE_CHEQUE              => AssistedDigitalModel::TYPE_CHEQUE
        ]);

        $this->add($field);
        $inputFilter->add($input);
    }
}
