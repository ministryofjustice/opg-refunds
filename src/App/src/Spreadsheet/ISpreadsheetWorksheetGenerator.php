<?php

namespace App\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

interface ISpreadsheetWorksheetGenerator
{
    /**
     * @param ClaimModel[] $claims the source data to generate the worksheet from. Should be a multidimensional array
     * @param UserModel $user
     * @return SpreadsheetWorksheet worksheet that can be passed to a compatible ISpreadsheetGenerator instance
     */
    public function generate(array $claims, UserModel $user): SpreadsheetWorksheet;
}
