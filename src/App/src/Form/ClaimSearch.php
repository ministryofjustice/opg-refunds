<?php

namespace App\Form;

use App\Filter\StandardInput as StandardInputFilter;
use App\View\Details\DetailsFormatterPlatesExtension;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
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
        $field = new Select('status');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->setRequired(false);

        $field->setValueOptions([
            '' => 'All',
            ClaimModel::STATUS_PENDING => DetailsFormatterPlatesExtension::getStatusText(ClaimModel::STATUS_PENDING),
            ClaimModel::STATUS_IN_PROGRESS => DetailsFormatterPlatesExtension::getStatusText(ClaimModel::STATUS_IN_PROGRESS),
            ClaimModel::STATUS_REJECTED => DetailsFormatterPlatesExtension::getStatusText(ClaimModel::STATUS_REJECTED),
            ClaimModel::STATUS_ACCEPTED => DetailsFormatterPlatesExtension::getStatusText(ClaimModel::STATUS_ACCEPTED)
        ]);

        $this->add($field);
        $inputFilter->add($input);
    }
}