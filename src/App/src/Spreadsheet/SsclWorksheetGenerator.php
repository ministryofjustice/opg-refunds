<?php

namespace App\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use DateTime;

class SsclWorksheetGenerator implements ISpreadsheetWorksheetGenerator
{
    const WORKSHEET_NAME = 'Data';

    /**
     * @var array
     */
    private $ssclConfig;

    public function __construct(array $ssclConfig)
    {
        $this->ssclConfig = $ssclConfig;
    }

    /**
     * @param ClaimModel[] $claims the source data to generate the worksheet from. Should be a multidimensional array
     * @return SpreadsheetWorksheet a complete SSCL schema compatible worksheet
     */
    public function generate(array $claims): SpreadsheetWorksheet
    {
        $rows = [];

        foreach ($claims as $idx => $claim) {
            $rowIndex = $idx + 3;
            $account = $claim->getApplication()->getAccount();
            $payment = $claim->getPayment();

            $cells = [];

            //OU
            $cells[] = new SpreadsheetCell(0, $rowIndex, 'MOJ');
            //Payee Type
            $cells[] = new SpreadsheetCell(3, $rowIndex, 'Client');
            //Unique Payee Reference
            $cells[] = new SpreadsheetCell(4, $rowIndex, $claim->getReferenceNumber());
            //Payee Name
            $cells[] = new SpreadsheetCell(5, $rowIndex, $account->getName());
            //Payee Address (use commas to separate)
            $cells[] = new SpreadsheetCell(6, $rowIndex, 'UNDEFINED');
            //Payee Postcode
            $cells[] = new SpreadsheetCell(7, $rowIndex, 'UNDEFINED');
            //Payment Method
            $cells[] = new SpreadsheetCell(10, $rowIndex, 'New Bank Details');
            //Sort Code
            $cells[] = new SpreadsheetCell(11, $rowIndex, $account->getSortCode());
            //Account Number
            $cells[] = new SpreadsheetCell(12, $rowIndex, $account->getAccountNumber());
            //Name of Bank - Not required by SSCL (Georgia confirmed on 21/09/2017)
            $cells[] = new SpreadsheetCell(13, $rowIndex, '');
            //Account Name
            $cells[] = new SpreadsheetCell(14, $rowIndex, $account->getName());
            //Roll Number
            $cells[] = new SpreadsheetCell(15, $rowIndex, 'N/A');
            //Invoice Date
            $cells[] = new SpreadsheetCell(16, $rowIndex, (new DateTime('today'))->format('d/m/Y'));
            //Invoice Number - Not required by SSCL (Georgia confirmed on 21/09/2017)
            $cells[] = new SpreadsheetCell(17, $rowIndex, '');
            //Description
            $cells[] = new SpreadsheetCell(18, $rowIndex, 'Lasting Power of Attorney');
            //Entity - From config
            $cells[] = new SpreadsheetCell(19, $rowIndex, $this->ssclConfig['entity']);
            //Cost Centre - From config
            $cells[] = new SpreadsheetCell(20, $rowIndex, $this->ssclConfig['cost_centre']);
            //Account - From config
            $cells[] = new SpreadsheetCell(21, $rowIndex, $this->ssclConfig['account']);
            //Objective - From config
            $cells[] = new SpreadsheetCell(22, $rowIndex, $this->ssclConfig['objective']);
            //Analysis - From config
            $cells[] = new SpreadsheetCell(23, $rowIndex, $this->ssclConfig['analysis']);
            //VAT Rate
            $cells[] = new SpreadsheetCell(24, $rowIndex, 'UK OUT OF SCOPE');
            //Net Amount
            $cells[] = new SpreadsheetCell(26, $rowIndex, $payment->getAmount());
            //VAT Amount
            $cells[] = new SpreadsheetCell(27, $rowIndex, 0);
            //Total Amount
            $cells[] = new SpreadsheetCell(28, $rowIndex, $payment->getAmount());
            //Completer ID - From config
            $cells[] = new SpreadsheetCell(29, $rowIndex, $this->ssclConfig['completer_id']);
            //Approver ID - From config
            $cells[] = new SpreadsheetCell(30, $rowIndex, $this->ssclConfig['approver_id']);

            $rows[] = new SpreadsheetRow($cells);
        }

        return new SpreadsheetWorksheet(self::WORKSHEET_NAME, $rows);
    }
}
