<?php

namespace App\Validator;

use Zend\Form\Element\Radio;

class NotEmptyIfRadioSelected extends NotEmpty
{
    /**
     * @var Radio
     */
    private $radioElement;
    /**
     * @var array
     */
    private $notEmptyValues;

    public function __construct(Radio $radioElement, array $notEmptyValues, $options = null)
    {
        parent::__construct($options);
        $this->radioElement = $radioElement;
        $this->notEmptyValues = $notEmptyValues;
    }

    public function isValid($value)
    {
        if (in_array($this->radioElement->getValue(), $this->notEmptyValues)) {
            return parent::isValid($value);
        }

        return true;
    }
}