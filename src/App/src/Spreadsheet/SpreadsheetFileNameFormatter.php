<?php

namespace App\Spreadsheet;

class SpreadsheetFileNameFormatter
{
    public function getFileName($schema, $fileFormat)
    {
        //TODO: Check if this functionality is required. I'm thinking for each day's spreadsheet name
        return 'OPG Multi-SOP1 Refund Requests.xls';
    }
}