<?php

namespace App\Spreadsheet;

class SsclWorksheetGenerator implements ISpreadsheetWorksheetGenerator
{
    const WORKSHEET_NAME = 'Data';

    /**
     * @param array $data the source data to generate the worksheet from. Should be a multidimensional array
     * @return SpreadsheetWorksheet a complete SSCL schema compatible worksheet
     */
    public function generate(array $data): SpreadsheetWorksheet
    {
        $rows = [];

        foreach ($data as $idx => $datum) {
            $rowIndex = $idx + 3;

            $cells = [];

            $cells[] = new SpreadsheetCell(4, $rowIndex, $datum['reference']);
            $cells[] = new SpreadsheetCell(5, $rowIndex, $datum['payeeName']);
            $cells[] = new SpreadsheetCell(11, $rowIndex, $datum['sortCode']);
            $cells[] = new SpreadsheetCell(12, $rowIndex, $datum['accountNumber']);
            $cells[] = new SpreadsheetCell(26, $rowIndex, $datum['amount']);
            $cells[] = new SpreadsheetCell(28, $rowIndex, $datum['amount']);

            $rows[] = new SpreadsheetRow($cells);
        }

        return new SpreadsheetWorksheet(self::WORKSHEET_NAME, $rows);
    }
}
