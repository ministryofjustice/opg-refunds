<?php

namespace App\Spreadsheet;

use DateTime;

class SpreadsheetFileNameFormatter
{
    public static function getFileName($schema, $fileFormat, $dateString)
    {
        return "BulkSOP1_MOJ_$dateString.xls";
    }

    public static function getTempFileName($schema, $fileFormat)
    {
        $timestamp = microtime(true);

        $fileName = "Temp_Spreadsheet_{$schema}_{$timestamp}";
        $fileName = str_replace('.', '-', $fileName);

        $fileExtension = strtolower($fileFormat);

        return "$fileName.$fileExtension";
    }

    public static function getClaimSearchFileName($fileFormat)
    {
        $timestamp = date('d-M-Y_H-i-s', (new DateTime())->getTimestamp());
        $fileExtension = strtolower($fileFormat);
        return "Search_results_{$timestamp}.{$fileExtension}";
    }
}
