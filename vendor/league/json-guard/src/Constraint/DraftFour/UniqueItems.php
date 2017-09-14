<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class UniqueItems implements ConstraintInterface
{
    const KEYWORD = 'uniqueItems';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'boolean', self::KEYWORD, $validator->getSchemaPath());

        if (!is_array($value) || $parameter === false) {
            return null;
        }

        if (count($value) === count(array_unique(array_map('serialize', $value)))) {
            return null;
        }

        return error('The array must be unique.', $validator);
    }
}
