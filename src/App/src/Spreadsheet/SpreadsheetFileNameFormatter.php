<?php

namespace App\Spreadsheet;

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
}
