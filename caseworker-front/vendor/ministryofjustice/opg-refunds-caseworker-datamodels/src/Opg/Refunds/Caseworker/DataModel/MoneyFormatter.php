<?php

namespace Opg\Refunds\Caseworker\DataModel;

/**
 * Class MoneyFormatter
 * @package Opg\Refunds\Caseworker\DataModel
 */
class MoneyFormatter
{
    /**
     * Returns the supplied float as a string prefixed by £ with two decimal places
     *
     * @param float $amount
     * @return string
     */
    public static function getMoneyString(float $amount): string
    {
        return money_format('£%i', $amount);
    }
}