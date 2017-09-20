<?php
namespace App\Form;

use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use Zend\Validator\Callback;
use Zend\Validator\ValidatorInterface;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class CaseNumber extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\Text('poa-case-number');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter)
            ->attach(new Filter\PregReplace([
                'pattern'     => '/-|\s/',
                'replacement' => '',
            ]));

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach(new Validator\Digits, true)
            ->attach($this->getOnlineLapValidator(), true)
            ->attach($this->getCaseNumberValidator(), true)
            ->attach((new Validator\StringLength(['max' => 12])));

        $this->add($field);
        $inputFilter->add($input);

        //---

        $field = new Element\Radio('have-poa-case-number');
        $input = new Input($field->getName());

        $input->getValidatorChain()->attach(new Validator\NotEmpty);

        $field->setValueOptions([
            'no' => 'no',
            'yes' => 'yes',
        ]);

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
    }

    public function getFormattedData()
    {
        // Filter out empty values
        return array_filter(parent::getData());
    }

    /**
     * Catches the case where an online LPA has been entered.
     * @return ValidatorInterface
     */
    private function getOnlineLapValidator() : ValidatorInterface
    {
        return (new Callback(function ($value) {
            return !(bool)preg_match('/^A(\d){11}$/', $value);
        }))->setMessage('lpa-tool-ref', Callback::INVALID_VALUE);
    }

    /**
     * Checks the value appears to be valid case number.
     * @return ValidatorInterface
     */
    private function getCaseNumberValidator() : ValidatorInterface
    {
        return (new Callback(function ($value) {
            if (!is_numeric($value)) {
                return false;
            }
            return (strlen($value) == 7 || strlen($value) == 12);
        }))->setMessage('invalid-case-number', Callback::INVALID_VALUE);
    }
}
