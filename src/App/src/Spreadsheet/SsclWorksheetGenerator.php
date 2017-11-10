<?php

namespace App\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;
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
     * @param UserModel $approver
     * @return SpreadsheetWorksheet a complete SSCL schema compatible worksheet
     */
    public function generate(array $claims, UserModel $approver): SpreadsheetWorksheet
    {
        $rows = [];

        foreach ($claims as $idx => $claim) {
            $rowIndex = $idx + 3;
            $donorCurrent = $claim->getApplication()->getDonor()->getCurrent();
            $addressArray = $donorCurrent->getAddress()->getArrayCopy();
            unset($addressArray['address-postcode']);
            $account = $claim->getApplication()->getAccount();
            $payment = $claim->getPayment();

            $cells = [];

            //OU
            $cells[] = new SpreadsheetCell(0, $rowIndex, 'MOJ');
            //Payee Type
            $cells[] = new SpreadsheetCell(3, $rowIndex, 'Client');
            //Unique Payee Reference
            $cells[] = new SpreadsheetCell(4, $rowIndex, $claim->getReferenceNumber());
            //Payee Forename
            $cells[] = new SpreadsheetCell(5, $rowIndex, $donorCurrent->getName()->getFirst());
            //Payee Surname
            $cells[] = new SpreadsheetCell(6, $rowIndex, $donorCurrent->getName()->getLast());
            //Payee Address Line 1
            $cells[] = new SpreadsheetCell(7, $rowIndex, $donorCurrent->getAddress()->getAddress1());
            //Payee Address Line 2
            //If address line 3 is blank we need to use address line 2 as the Town/City value and so blank it here
            $payeeAddressLine2 = empty($donorCurrent->getAddress()->getAddress3()) ? '' : $donorCurrent->getAddress()->getAddress2();
            $cells[] = new SpreadsheetCell(8, $rowIndex, $payeeAddressLine2);
            //Town/City
            $payeeAddressLine3 = empty($donorCurrent->getAddress()->getAddress3()) ? $donorCurrent->getAddress()->getAddress2() : $donorCurrent->getAddress()->getAddress3();
            $cells[] = new SpreadsheetCell(9, $rowIndex, $payeeAddressLine3);
            //Payee Postcode
            $cells[] = new SpreadsheetCell(10, $rowIndex, $donorCurrent->getAddress()->getAddressPostcode());
            //Payment Method
            $cells[] = new SpreadsheetCell(13, $rowIndex, 'New Bank Details');
            //Sort Code
            $cells[] = new SpreadsheetCell(14, $rowIndex, $account->getSortCode());
            //Account Number
            $cells[] = new SpreadsheetCell(15, $rowIndex, $account->getAccountNumber());
            //Name of Bank
            $cells[] = new SpreadsheetCell(16, $rowIndex, SortCodeMapper::getNameOfBank($account->getSortCode()));
            //Account Name
            $cells[] = new SpreadsheetCell(17, $rowIndex, $account->getName());
            //Roll Number - Should be blank rather than N/A - SSCL confirmed on 10/11/2017
            $cells[] = new SpreadsheetCell(18, $rowIndex, '');
            //Invoice Date
            $cells[] = new SpreadsheetCell(19, $rowIndex, (new DateTime('today'))->format('d/m/Y'));
            //Invoice Number - Programme board instructed to use reference number on 02/11/2017
            $cells[] = new SpreadsheetCell(20, $rowIndex, $claim->getReferenceNumber());
            //Description
            $cells[] = new SpreadsheetCell(21, $rowIndex, 'Lasting Power of Attorney');
            //Entity - From config
            $cells[] = new SpreadsheetCell(22, $rowIndex, $this->ssclConfig['entity']);
            //Cost Centre - From config
            $cells[] = new SpreadsheetCell(23, $rowIndex, $this->ssclConfig['cost_centre']);
            //Account - From config
            $cells[] = new SpreadsheetCell(24, $rowIndex, $this->ssclConfig['account']);
            //Objective - From config
            $cells[] = new SpreadsheetCell(25, $rowIndex, $this->ssclConfig['objective']);
            //Analysis - From config
            $cells[] = new SpreadsheetCell(26, $rowIndex, $this->ssclConfig['analysis']);
            //VAT Rate
            $cells[] = new SpreadsheetCell(27, $rowIndex, 'UK OUT OF SCOPE');
            //Net Amount
            $cells[] = new SpreadsheetCell(29, $rowIndex, $payment->getAmount());
            //VAT Amount
            $cells[] = new SpreadsheetCell(30, $rowIndex, 0);
            //Total Amount
            $cells[] = new SpreadsheetCell(31, $rowIndex, $payment->getAmount());
            //Completer ID - From passed in user or overridden by config
            $completerId = !empty($this->ssclConfig['completer_id']) ? $this->ssclConfig['completer_id'] : $claim->getFinishedByName();
            $cells[] = new SpreadsheetCell(32, $rowIndex, $completerId);
            //Approver ID - From passed in user or overridden by config
            $approverId = !empty($this->ssclConfig['approver_id']) ? $this->ssclConfig['approver_id'] : $approver->getName();
            $cells[] = new SpreadsheetCell(33, $rowIndex, $approverId);

            $rows[] = new SpreadsheetRow($cells);
        }

        return new SpreadsheetWorksheet(self::WORKSHEET_NAME, $rows);
    }
}
