<?php

namespace App\Spreadsheet;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

interface ISpreadsheetWorksheetGenerator
{
    /**
     * @param DateTime $date
     * @param ClaimModel[] $claims the source data to generate the worksheet from. Should be a multidimensional array
     * @param UserModel $approver
     * @return SpreadsheetWorksheet worksheet that can be passed to a compatible ISpreadsheetGenerator instance
     */
    public function generate(DateTime $date, array $claims, UserModel $approver): SpreadsheetWorksheet;

    /**
     * Returns an array containing the hash of each row referenced by the claim code.
     * Used for verifying no changes have been made to a row
     *
     * @param SpreadsheetWorksheet $spreadsheetWorksheet
     * @return array indexed by claim code and with the hash as the value
     */
    public function getHashes(SpreadsheetWorksheet $spreadsheetWorksheet): array;

    public function getWorksheet(array $worksheetData): SpreadsheetWorksheet;
}
