<?php

namespace Opg\Refunds\Caseworker\DataModel;

/**
 * For formatting Refund Application IDs, ready for the user.
 *
 * Class IdentFormatter
 * @package App\Service
 */
class IdentFormatter
{
    /**
     * Formats the id as an R, followed by 11 digits, split into 3 blocks of 4 characters.
     *
     * For example: 'R010 1234 5678'
     *
     * @param int $value The application's id.
     * @return string The formatted value.
     */
    public static function format(int $value)
    {
        return trim(chunk_split('R' . sprintf("%011d", $value), 4, ' '));
    }
}
