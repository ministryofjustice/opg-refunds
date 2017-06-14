<?php
namespace App\Validator;

use Zend\Validator\ValidatorInterface;

/**
 * Allows any Zend Validator to validate an empty value as valid.
 * Non-empty values are passed to the original validator.
 *
 * Class AllowEmptyValidatorWrapper
 * @package App\Validator
 */
class AllowEmptyValidatorWrapper implements ValidatorInterface
{

    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function isValid($value)
    {
        if (empty($value)) {
            return true;
        }

        return $this->validator->isValid($value);
    }

    public function getMessages()
    {
        return $this->validator->getMessages();
    }
}
