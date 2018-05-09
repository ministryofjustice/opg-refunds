<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class ExclusiveMaximum implements ConstraintInterface
{
    const KEYWORD = 'exclusiveMaximum';

    /**
     * @var int|null
     */
    private $precision;

    /**
     * @param int|null $precision
     */
    public function __construct($precision = 10)
    {
        $this->precision = $precision;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $parameter, Validator $validator)
    {
        Assert::type($parameter, 'boolean', self::KEYWORD, $validator->getSchemaPath());
        Assert::hasProperty($validator->getSchema(), 'maximum', self::KEYWORD, $validator->getSchemaPath());

        if ($parameter !== true) {
            return null;
        }

        if (!is_numeric($value) || bccomp($value, $validator->getSchema()->maximum, $this->precision) === -1) {
            return null;
        }

        return error('The number must be less than {parameter}.', $validator);
    }
}
