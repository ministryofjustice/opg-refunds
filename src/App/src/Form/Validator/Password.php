<?php

namespace App\Form\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;

/**
 * Class Password
 *
 * @package App\Form\Validator
 */
class Password extends AbstractValidator
{
    const TOO_SHORT                      = 'tooShort';
    const MUST_INCLUDE_DIGIT             = 'mustIncludeDigit';
    const MUST_INCLUDE_LOWER_CASE        = 'mustIncludeLowerCase';
    const MUST_INCLUDE_UPPER_CASE        = 'mustIncludeUpperCase';
    const MUST_INCLUDE_SPECIAL_CHARACTER = 'mustIncludeSpecialCharacter';

    protected $messageTemplates = [
        self::TOO_SHORT                      => 'too-short',
        self::MUST_INCLUDE_DIGIT             => 'must-include-digit',
        self::MUST_INCLUDE_LOWER_CASE        => 'must-include-lower-case',
        self::MUST_INCLUDE_UPPER_CASE        => 'must-include-upper-case',
        self::MUST_INCLUDE_SPECIAL_CHARACTER => 'must-include-special-character',
    ];

    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        //  Check value is long enough
        $stringLengthValidator = new StringLength(8);

        if (!$stringLengthValidator->isValid($value)) {
            $this->error(self::TOO_SHORT);
            $isValid = false;
        }

        //  Check that a number has been provided
        $regExValidator = new Regex('/.*[0-9].*/');

        if (!$regExValidator->isValid($value)) {
            $this->error(self::MUST_INCLUDE_DIGIT);
            $isValid = false;
        }

        //  Check that a lower case letter has been provided
        $regExValidator = new Regex('/.*[a-z].*/');

        if (!$regExValidator->isValid($value)) {
            $this->error(self::MUST_INCLUDE_LOWER_CASE);
            $isValid = false;
        }

        //  Check that an upper case letter has been provided
        $regExValidator = new Regex('/.*[A-Z].*/');

        if (!$regExValidator->isValid($value)) {
            $this->error(self::MUST_INCLUDE_UPPER_CASE);
            $isValid = false;
        }

        //  Check that a special character has been provided
        $regExValidator = new Regex('/.*[^A-Za-z0-9].*/');

        if (!$regExValidator->isValid($value)) {
            $this->error(self::MUST_INCLUDE_SPECIAL_CHARACTER);
            $isValid = false;
        }

        return $isValid;
    }
}
