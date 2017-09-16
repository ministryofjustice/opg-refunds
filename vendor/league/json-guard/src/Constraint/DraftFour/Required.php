<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class Required implements ConstraintInterface
{
    const KEYWORD = 'required';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value)) {
            return null;
        }

        $actualProperties = array_keys(get_object_vars($value));
        $missing          = array_diff($parameter, $actualProperties);
        if (count($missing)) {
            return error('The object must contain the properties {cause}.', $validator)
                ->withCause(array_values($missing));
        }

        return null;
    }
}
