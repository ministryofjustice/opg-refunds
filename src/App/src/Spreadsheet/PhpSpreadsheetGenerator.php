<?php

namespace App\Spreadsheet;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;

class PhpSpreadsheetGenerator implements ISpreadsheetGenerator
{
    const SPREADSHEET_SOURCE_FOLDER = 'content/';

    public function generate($schema, $fileFormat, $data)
    {
        if ($schema !== ISpreadsheetGenerator::SCHEMA_SSCL || $fileFormat !== ISpreadsheetGenerator::FILE_FORMAT_XLS) {
            throw new InvalidArgumentException('Supplied schema and file format is not supported');
        }

        $reader = new XlsReader();
        $ssclSourceSpreadsheetFilename = __DIR__ . '/../../../../' . self::SPREADSHEET_SOURCE_FOLDER . 'OPG Multi-SOP1 Refund Requests.xls';
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
        $writer->save(__DIR__ . '/../../../../' . self::SPREADSHEET_SOURCE_FOLDER . 'test.xls');

        //$writer->save('/some/path/to/file.xlsx'); // standard, save to disk
        //$writer->save('php://output');            // to download the file in the browser
        //$handle = $writer->save('php://memory');  // to get a file descriptor to the stream in memory
    }
}