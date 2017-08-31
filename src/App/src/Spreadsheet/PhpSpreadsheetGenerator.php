<?php

namespace App\Spreadsheet;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;

class PhpSpreadsheetGenerator implements ISpreadsheetGenerator
{
    const SPREADSHEET_OUTPUT_FOLDER = __DIR__ . '/../../../../content/';

    const SSCL_SPREADSHEET_SOURCE_FILENAME = __DIR__ . '/../../../../content/OPG Multi-SOP1 Refund Requests.xls';

    public function generate($schema, $fileFormat, $data)
    {
        if ($schema !== ISpreadsheetGenerator::SCHEMA_SSCL || $fileFormat !== ISpreadsheetGenerator::FILE_FORMAT_XLS) {
            throw new InvalidArgumentException('Supplied schema and file format is not supported');
        }

        $reader = new XlsReader();
        $ssclSourceSpreadsheetFilename = self::SSCL_SPREADSHEET_SOURCE_FILENAME;
        $ssclSourceSpreadsheet = $reader->load($ssclSourceSpreadsheetFilename);

        $dataSheet = $ssclSourceSpreadsheet->getSheetByName('Data');

        foreach ($data as $idx => $row) {
            $dataSheet->setCellValueByColumnAndRow(4, $idx + 3, $row['reference']);
            $dataSheet->setCellValueByColumnAndRow(5, $idx + 3, $row['payeeName']);
            $dataSheet->setCellValueByColumnAndRow(11, $idx + 3, $row['sortCode']);
            $dataSheet->setCellValueByColumnAndRow(12, $idx + 3, $row['accountNumber']);
            $dataSheet->setCellValueByColumnAndRow(26, $idx + 3, $row['amount']);
            $dataSheet->setCellValueByColumnAndRow(28, $idx + 3, $row['amount']);
        }

        //$cell = $dataSheet->getCell('C2');

        $writer = new XlsWriter($ssclSourceSpreadsheet);
        $writer->save(self::SPREADSHEET_OUTPUT_FOLDER . 'test.xls');

        //$writer->save('/some/path/to/file.xlsx'); // standard, save to disk
        //$writer->save('php://output');            // to download the file in the browser
        //$handle = $writer->save('php://memory');  // to get a file descriptor to the stream in memory

        throw new InvalidArgumentException('Supplied schema and file format is not supported');
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
        if ($schema === ISpreadsheetGenerator::SCHEMA_SSCL && $fileFormat === ISpreadsheetGenerator::FILE_FORMAT_XLS) {
            $outputFilePath = self::SPREADSHEET_OUTPUT_FOLDER . $fileName;

            $reader = new XlsReader();
            $ssclSourceSpreadsheetFilename = self::SSCL_SPREADSHEET_SOURCE_FILENAME;
            $ssclSourceSpreadsheet = $reader->load($ssclSourceSpreadsheetFilename);

            $dataSheet = $ssclSourceSpreadsheet->getSheetByName($spreadsheetWorksheet->getName());

            foreach ($spreadsheetWorksheet->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $dataSheet->setCellValueByColumnAndRow($cell->getColumn(), $cell->getRow(), $cell->getData());
                }
            }

            $writer = new XlsWriter($ssclSourceSpreadsheet);
            $writer->save($outputFilePath);

            return $outputFilePath;
        }

        throw new InvalidArgumentException('Supplied schema and file format is not supported');
    }
}