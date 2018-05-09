<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;
use function League\JsonGuard\pointer_push;

final class AnyOf implements ConstraintInterface
{
    const KEYWORD = 'anyOf';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'array', self::KEYWORD, $validator->getSchemaPath());
        Assert::notEmpty($parameter, self::KEYWORD, $validator->getSchemaPath());

        foreach ($parameter as $key => $schema) {
            $subValidator = $validator->makeSubSchemaValidator(
                $value,
                $schema,
                $validator->getDataPath(),
                pointer_push($validator->getSchemaPath(), $key)
            );
            if ($subValidator->passes()) {
                return null;
            }
        }
        return error('The data must match one of the schemas.', $validator);
    }
}
