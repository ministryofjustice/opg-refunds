<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard;
use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\pointer_push;

final class PatternProperties implements ConstraintInterface
{
    const KEYWORD = 'patternProperties';

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'object', self::KEYWORD, $validator->getSchemaPath());

        if (!is_object($value)) {
            return null;
        }

        $errors = [];
        foreach ($parameter as $property => $schema) {
            $matches = JsonGuard\properties_matching_pattern($property, $value);
            foreach ($matches as $match) {
                $subValidator = $validator->makeSubSchemaValidator(
                    $value->$match,
                    $schema,
                    pointer_push($validator->getDataPath(), $match),
                    pointer_push($validator->getSchemaPath(), $property)
                );
                $errors = array_merge($errors, $subValidator->errors());
            }
        }
        return $errors;
    }
}
