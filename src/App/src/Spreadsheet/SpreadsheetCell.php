<?php

namespace App\Spreadsheet;

class SpreadsheetCell
{
    /**
     * @var int
     */
    private $column;
    /**
     * @var int
     */
    private $row;
    /**
     * @var string
     */
    private $data;

    public function __construct(int $column, int $row, string $data)
    {
        $this->column = $column;
        $this->row = $row;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
}