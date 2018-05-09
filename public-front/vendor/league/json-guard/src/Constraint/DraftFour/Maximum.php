<?php

namespace League\JsonGuard\Constraint\DraftFour;

use League\JsonGuard\Assert;
use League\JsonGuard\ConstraintInterface;
use League\JsonGuard\Validator;
use function League\JsonGuard\error;

final class Maximum implements ConstraintInterface
{
    const KEYWORD = 'maximum';

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
        Assert::type($parameter, 'number', self::KEYWORD, $validator->getSchemaPath());

        if (isset($validator->getSchema()->exclusiveMaximum) && $validator->getSchema()->exclusiveMaximum === true) {
            return;
        }

        if (!is_numeric($value) ||
            bccomp($value, $parameter, $this->precision) === -1 || bccomp($value, $parameter, $this->precision) === 0) {
            return null;
        }

        return error('The number must be less than {parameter}.', $validator);
    }
}
