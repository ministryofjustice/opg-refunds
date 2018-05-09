<?php

namespace App\Validator;

use Zend\Form\ElementInterface;
use Zend\Validator\AbstractValidator;

class InvalidValueCombination extends AbstractValidator
{
    const INVALID  = 'invalid-combination';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => self::INVALID,
    ];

    /**
     * @var ElementInterface
     */
    private $element;

    /**
     * @var ElementInterface
     */
    private $dependentElement;

    /**
     * @var array
     */
    private $invalidCombination;

    public function __construct(
        ElementInterface $element,
        ElementInterface $dependentElement,
        array $invalidCombination,
        $options = null
    ) {
        parent::__construct($options);
        $this->element = $element;
        $this->dependentElement = $dependentElement;
        $this->invalidCombination = $invalidCombination;
    }

    public function isValid($value)
    {
        $arr = [
            'value' => $this->element->getValue(),
            'dependentValue' => $this->dependentElement->getValue()
        ];
        if ($this->invalidCombination == $arr) {
            $this->error(self::INVALID);
            return false;
        }

        return true;
    }
}