<?php

namespace App\Spreadsheet;

class SpreadsheetFileNameFormatter
{
    public static function getFileName($schema, $fileFormat)
    {
        //TODO: Check if this functionality is required. I'm thinking for each day's spreadsheet name
        return 'OPG Multi-SOP1 Refund Requests.xls';
    }

    public static function getTempFileName($schema, $fileFormat)
    {
        $timestamp = '' . microtime(true);
        $timestamp = str_pad($timestamp, 15, '0');

        $fileName = "Temp_Spreadsheet_{$schema}_{$timestamp}";
        $fileName = str_replace('.', '-', $fileName);

        $fileExtension = strtolower($fileFormat);

        return "$fileName.$fileExtension";
    }
}
