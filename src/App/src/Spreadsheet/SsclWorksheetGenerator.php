<?php

namespace App\Spreadsheet;

use App\DataModel\Cases\RefundCase as CaseDataModel;
use App\Service\IdentFormatter;
use DateTime;

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

            $cells[] = new SpreadsheetCell(0, $rowIndex, 'MOJ');
            $cells[] = new SpreadsheetCell(3, $rowIndex, 'Client');
            $cells[] = new SpreadsheetCell(4, $rowIndex, IdentFormatter::format($case->getId()));
            $cells[] = new SpreadsheetCell(5, $rowIndex, $account->getName());
            $cells[] = new SpreadsheetCell(6, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(7, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(10, $rowIndex, 'New Bank Details');
            $cells[] = new SpreadsheetCell(11, $rowIndex, $account->getSortCode());
            $cells[] = new SpreadsheetCell(12, $rowIndex, $account->getAccountNumber());
            $cells[] = new SpreadsheetCell(13, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(14, $rowIndex, $account->getName());
            $cells[] = new SpreadsheetCell(15, $rowIndex, 'N/A');
            $cells[] = new SpreadsheetCell(16, $rowIndex, (new DateTime('today'))->format('d/m/Y'));
            $cells[] = new SpreadsheetCell(17, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(18, $rowIndex, 'Lasting Power of Attorney');
            $cells[] = new SpreadsheetCell(19, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(20, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(21, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(22, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(23, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(24, $rowIndex, 'UK OUT OF SCOPE');
            $cells[] = new SpreadsheetCell(26, $rowIndex, $payment->getAmount());
            $cells[] = new SpreadsheetCell(27, $rowIndex, 0);
            $cells[] = new SpreadsheetCell(28, $rowIndex, $payment->getAmount());
            $cells[] = new SpreadsheetCell(29, $rowIndex, 'UNDEFINED');
            $cells[] = new SpreadsheetCell(30, $rowIndex, 'UNDEFINED');

            $rows[] = new SpreadsheetRow($cells);
        }

        return new SpreadsheetWorksheet(self::WORKSHEET_NAME, $rows);
    }
}
