<?php

namespace App\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Cases\ClaimSummary;
use Psr\Http\Message\StreamInterface;

interface ISpreadsheetGenerator
{
    const SCHEMA_SSCL = 'SSCL';

    const FILE_FORMAT_CSV = 'CSV';
    const FILE_FORMAT_XLS = 'XLS';
    const FILE_FORMAT_XLSX = 'XLSX';

    /**
     * @param string $schema The schema the produced spreadsheet should follow e.g. SSCL
     * @param string $fileFormat The file format of the resulting stream e.g. XLS
     * @param string $fileName The desired name of the generated spreadsheet file
     * @param SpreadsheetWorksheet $spreadsheetWorksheet the data to be written to the spreadsheet
     * @return string full path of the generated spreadsheet file
     */
    public function generateFile(
        string $schema,
        string $fileFormat,
        string $fileName,
        SpreadsheetWorksheet $spreadsheetWorksheet
    ): string;

    /**
     * @param string $schema The schema the produced spreadsheet should follow e.g. SSCL
     * @param string $fileFormat The file format of the resulting stream e.g. XLS
     * @param SpreadsheetWorksheet $spreadsheetWorksheet the data to be written to the spreadsheet
     * @return bool|resource a file pointer resource on success, or false on error.
     */
    public function generateStream(
        string $schema,
        string $fileFormat,
        SpreadsheetWorksheet $spreadsheetWorksheet
    );

    public function deleteTempFiles();

    /**
     * @param StreamInterface $spreadsheetStream
     * @return array
     */
    public function getWorksheetData(StreamInterface $spreadsheetStream): array;

    /**
     * @param string $fileFormat The file format of the resulting stream e.g. XLSX
     * @param ClaimSummary[] $claimSummaries
     * @param array $queryParameters
     * @return bool|resource a file pointer resource on success, or false on error.
     */
    public function getClaimSearchStream(string $fileFormat, array $claimSummaries, array $queryParameters);
}
