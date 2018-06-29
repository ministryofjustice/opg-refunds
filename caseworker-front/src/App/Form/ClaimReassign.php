<?php

namespace App\Form;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;
use Opg\Refunds\Caseworker\DataModel\Cases\UserSummary as UserSummaryModel;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class ClaimReassign
 * @package App\Form
 */
class ClaimReassign extends AbstractForm
{
    public function __construct(array $options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //  Csrf field
        $this->addCsrfElement($inputFilter);

        //  User selection
        $field = new Select('user-id');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty);

        $input->setRequired(true);

        /* @var UserSummaryModel[] $userSummaries */
        $userSummaries = $options['userSummaries'];

        $valueOptions = [];
        foreach ($userSummaries as $userSummary) {
            $valueOptions[$userSummary->getId()] = $userSummary->getName();
        }

        $field->setValueOptions($valueOptions);

        $this->add($field);
        $inputFilter->add($input);

        //  Reason field
        $field = new Textarea('reason');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);
    }
}