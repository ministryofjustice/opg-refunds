<?php

namespace App\Spreadsheet;

class SpreadsheetWorksheet
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var SpreadsheetRow[]
     */
    private $rows;

    /**
     * SpreadsheetWorksheet constructor.
     * @param string $name the name of the worksheet as displayed in the source file
     * @param SpreadsheetRow[] $rows
     */
    public function __construct(string $name, array $rows)
    {
        $this->name = $name;
        $this->rows = $rows;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return SpreadsheetRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}
