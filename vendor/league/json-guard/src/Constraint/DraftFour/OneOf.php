<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;
use function League\JsonGuard\pointer_push;

final class OneOf implements ConstraintInterface
{
    const KEYWORD = 'oneOf';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        $passed = 0;
        foreach ($parameter as $key => $schema) {
            $subValidator = $validator->makeSubSchemaValidator(
                $value,
                $schema,
                $validator->getDataPath(),
                pointer_push($validator->getSchemaPath(), $key)
            );
            if ($subValidator->passes()) {
                $passed++;
            }
        }
        if ($passed !== 1) {
            return error('The data must match exactly one of the schemas.', $validator);
        }

        return null;
    }
}
