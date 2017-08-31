<?php

namespace App\Spreadsheet;

interface ISpreadsheetGenerator
{
    const SCHEMA_SSCL = 'SSCL';

    const FILE_FORMAT_CSV = 'CSV';
    const FILE_FORMAT_XLS = 'XLS';
    const FILE_FORMAT_XLSX = 'XLS';

    /**
     * @param string $schema The schema the produced spreadsheet should follow e.g. SSCL
     * @param string $fileFormat The file format of the resulting stream e.g. XLS
     * @param array $data the data to be written to the spreadsheet. Should be a multidimensional array
     * @return mixed
     */
    public function generate($schema, $fileFormat, $data);
}