<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/08/17
 * Time: 17:53
 */

namespace App\Spreadsheet;


class SpreadsheetWorksheet
{
    /**
     * @var SpreadsheetRow[]
     */
    private $rows;

    /**
     * SpreadsheetWorksheet constructor.
     * @param SpreadsheetRow[] $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return SpreadsheetRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}