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

    /**
     * Parse the claim's id from the code
     *
     * @param string $claimCode
     * @return bool|int
     */
    public static function parseId(string $claimCode)
    {
        $claimCode = str_replace(' ', '', $claimCode);
        $claimCode = str_ireplace('R', '', $claimCode);

        return is_numeric($claimCode) ? (int)$claimCode : false;
    }
}
