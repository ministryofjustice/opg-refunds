<?php

namespace App\Spreadsheet;

use App\DataModel\Cases\RefundCase as CaseDataModel;

class SsclWorksheetGenerator implements ISpreadsheetWorksheetGenerator
{
    const WORKSHEET_NAME = 'Data';

    /**
     * @param CaseDataModel[] $cases the source data to generate the worksheet from. Should be a multidimensional array
     * @return SpreadsheetWorksheet a complete SSCL schema compatible worksheet
     */
    public function generate(array $cases): SpreadsheetWorksheet
    {
        $rows = [];

        foreach ($cases as $idx => $case) {
            $rowIndex = $idx + 3;
            $account = $case->getApplication()->getAccount();
            $payment = $case->getPayment();

            $cells = [];

            $cells[] = new SpreadsheetCell(4, $rowIndex, $case->getId());
            $cells[] = new SpreadsheetCell(5, $rowIndex, $account->getName());
            $cells[] = new SpreadsheetCell(11, $rowIndex, $account->getSortCode());
            $cells[] = new SpreadsheetCell(12, $rowIndex, $account->getAccountNumber());
            $cells[] = new SpreadsheetCell(26, $rowIndex, $payment->getAmount());
            $cells[] = new SpreadsheetCell(28, $rowIndex, $payment->getAmount());

            $rows[] = new SpreadsheetRow($cells);
        }

        return new SpreadsheetWorksheet(self::WORKSHEET_NAME, $rows);
    }
}
