<?php

namespace App\Form;

use App\Filter\StandardInput as StandardInputFilter;
use App\Validator;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\UserSummary as UserSummaryModel;
use Opg\Refunds\Caseworker\DataModel\StatusFormatter;
use Zend\Form\Element\Select;
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

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //  Status selection
        $field = new Select('statuses');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->setRequired(false);

        $field->setValueOptions([
            '' => 'All',
            join(',', [ClaimModel::STATUS_DUPLICATE, ClaimModel::STATUS_REJECTED, ClaimModel::STATUS_ACCEPTED, ClaimModel::STATUS_WITHDRAWN]) => 'Completed',
            ClaimModel::STATUS_PENDING => StatusFormatter::getStatusText(ClaimModel::STATUS_PENDING),
            ClaimModel::STATUS_IN_PROGRESS => StatusFormatter::getStatusText(ClaimModel::STATUS_IN_PROGRESS),
            ClaimModel::STATUS_DUPLICATE => StatusFormatter::getStatusText(ClaimModel::STATUS_DUPLICATE),
            ClaimModel::STATUS_REJECTED => StatusFormatter::getStatusText(ClaimModel::STATUS_REJECTED),
            ClaimModel::STATUS_ACCEPTED => StatusFormatter::getStatusText(ClaimModel::STATUS_ACCEPTED),
            ClaimModel::STATUS_WITHDRAWN => StatusFormatter::getStatusText(ClaimModel::STATUS_WITHDRAWN),
            'outcome_changed' => 'Outcome Changed'
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //  User selection
        $field = new Select('assignedToFinishedById');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->setRequired(false);

        /* @var UserSummaryModel[] $userSummaries */
        $userSummaries = $options['userSummaries'];

        $valueOptions = ['' => 'Any'];
        foreach ($userSummaries as $userSummary) {
            $valueOptions[$userSummary->getId()] = $userSummary->getName();
        }

        $field->setValueOptions($valueOptions);

        $this->add($field);
        $inputFilter->add($input);
    }
}