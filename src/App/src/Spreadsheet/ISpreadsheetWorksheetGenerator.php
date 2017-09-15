<?php

namespace App\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase as CaseDataModel;

interface ISpreadsheetWorksheetGenerator
{
    /**
     * @param CaseDataModel[] $cases the source data to generate the worksheet from. Should be a multidimensional array
     * @return SpreadsheetWorksheet worksheet that can be passed to a compatible ISpreadsheetGenerator instance
     */
    public function generate(array $cases): SpreadsheetWorksheet;
}
