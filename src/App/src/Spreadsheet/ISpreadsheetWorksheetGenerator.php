<?php

namespace App\Spreadsheet;

interface ISpreadsheetWorksheetGenerator
{
    /**
     * @param array $data the source data to generate the worksheet from. Should be a multidimensional array
     * @return SpreadsheetWorksheet a complete worksheet that can be passed to a compatible ISpreadsheetGenerator instance
     */
    public function generate(array $data): SpreadsheetWorksheet;
}