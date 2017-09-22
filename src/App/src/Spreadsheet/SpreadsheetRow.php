<?php

namespace App\Spreadsheet;

class SpreadsheetRow
{
    /**
     * @var SpreadsheetCell[]
     */
    private $cells;

    /**
     * SpreadsheetRow constructor.
     * @param SpreadsheetCell[] $cells
     */
    public function __construct(array $cells)
    {
        $this->cells = $cells;
    }

    /**
     * @return SpreadsheetCell[]
     */
    public function getCells(): array
    {
        return $this->cells;
    }
}
