<?php

namespace App\Spreadsheet;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;

class PhpSpreadsheetGenerator implements ISpreadsheetGenerator
{
    /**
     * @var string
     */
    private $sourceFolder;
    /**
     * @var string
     */
    private $tempFolder;

    public function __construct(string $sourceFolder, string $tempFolder)
    {
        $this->sourceFolder = $sourceFolder;
        $this->tempFolder = $tempFolder;

        if (substr($this->sourceFolder, -1) !== '/') {
            $this->sourceFolder .= '/';
        }
        if (substr($this->tempFolder, -1) !== '/') {
            $this->tempFolder .= '/';
        }

        if (!file_exists($tempFolder)) {
            mkdir($tempFolder, 0777, true);
        }
    }

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
    ): string {
        $outputFilePath = $this->tempFolder . $fileName;
        if (file_exists($outputFilePath)) {
            throw new InvalidArgumentException('Supplied filename already exists in temp folder');
        }

        if ($schema === ISpreadsheetGenerator::SCHEMA_SSCL && $fileFormat === ISpreadsheetGenerator::FILE_FORMAT_XLS) {
            $reader = new XlsReader();
            $ssclSourceSpreadsheetFilename = $this->sourceFolder . 'OPG Multi-SOP1 Refund Requests.xls';
            $ssclSourceSpreadsheet = $reader->load($ssclSourceSpreadsheetFilename);

            $dataSheet = $ssclSourceSpreadsheet->getSheetByName($spreadsheetWorksheet->getName());

            foreach ($spreadsheetWorksheet->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $dataSheet->setCellValueByColumnAndRow($cell->getColumn(), $cell->getRow(), $cell->getData());
                }
            }

            $writer = new XlsWriter($ssclSourceSpreadsheet);
            $writer->save($outputFilePath);
            //$writer->save('php://output');            // to download the file in the browser

            return $outputFilePath;
        }

        throw new InvalidArgumentException('Supplied schema and file format is not supported');
    }

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
    ) {
        $tempFileName = SpreadsheetFileNameFormatter::getTempFileName($schema, $fileFormat);

        $outputFilePath = $this->generateFile($schema, $fileFormat, $tempFileName, $spreadsheetWorksheet);

        $handle = fopen($outputFilePath, 'r');

        return $handle;
    }

    public function deleteTempFiles()
    {
        $files = scandir($this->tempFolder);
        foreach ($files as $file) {
            if (is_file($this->tempFolder . $file)) {
                unlink($this->tempFolder . $file);
            }
        }
    }
}
