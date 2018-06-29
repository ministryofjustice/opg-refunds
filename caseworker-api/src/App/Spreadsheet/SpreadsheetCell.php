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
     * @var string|int
     */
    private $data;

    /**
     * SpreadsheetCell constructor.
     * @param int $column
     * @param int $row
     * @param string|int $data
     */
    public function __construct(int $column, int $row, $data)
    {
        $this->column = $column;
        $this->row = $row;
        if (is_string($data)) {
            //Only set 'NOT SUPPLIED' if data is explicitly null
            if ($data === null) {
                $this->data = 'NOT SUPPLIED';
            } else {
                $this->data = $data;
            }
        } else {
            $this->data = $data;
        }
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
     * @return int|string
     */
    public function getData()
    {
        return $this->data;
    }
}
