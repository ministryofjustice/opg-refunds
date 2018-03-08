<?php

namespace App\Spreadsheet;

use App\Exception\InvalidInputException;
use Exception;
use InvalidArgumentException;
use Opg\Refunds\Caseworker\DataModel\Cases\ClaimSummary;
use Opg\Refunds\Caseworker\DataModel\StatusFormatter;
use Opg\Refunds\Log\Initializer;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Psr\Http\Message\StreamInterface;

class PhpSpreadsheetGenerator implements ISpreadsheetGenerator, Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

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

        $this->getLogger()->info("Generating spreadsheet with schema {$schema}, file format {$fileFormat} and file name {$fileName} in {$outputFilePath}");

        if ($schema === ISpreadsheetGenerator::SCHEMA_SSCL && $fileFormat === ISpreadsheetGenerator::FILE_FORMAT_XLS) {
            $start = microtime(true);

            $reader = new XlsxReader();
            //$reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly($spreadsheetWorksheet->getName());
            $ssclSourceSpreadsheetFilename = $this->sourceFolder . 'BulkSOP1 MOJ No Formatting.xlsx';
            $ssclSourceSpreadsheet = $reader->load($ssclSourceSpreadsheetFilename);

            $this->getLogger()->debug('Spreadsheet file ' . $ssclSourceSpreadsheetFilename . ' loaded in ' . $this->getElapsedTimeInMs($start) . 'ms');

            $dataSheet = $ssclSourceSpreadsheet->getSheetByName($spreadsheetWorksheet->getName());

            $rowStart = microtime(true);

            foreach ($spreadsheetWorksheet->getRows() as $row) {
                $start = microtime(true);

                foreach ($row->getCells() as $cell) {
                    $dataSheet->setCellValueByColumnAndRow($cell->getColumn() + 1, $cell->getRow(), $cell->getData());
                }

                $this->getLogger()->debug('Spreadsheet row set in ' . $this->getElapsedTimeInMs($start) . 'ms');
            }

            $this->getLogger()->debug('All spreadsheet rows set in ' . $this->getElapsedTimeInMs($rowStart) . 'ms');

            $start = microtime(true);

            $writer = new XlsWriter($ssclSourceSpreadsheet);
            $writer->save($outputFilePath);

            $this->getLogger()->debug('Spreadsheet file ' . $outputFilePath . ' written in ' . $this->getElapsedTimeInMs($start) . 'ms');

            $this->getLogger()->info("Successfully generated spreadsheet with schema {$schema}, file format {$fileFormat} and file name {$fileName} to {$outputFilePath}");

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

    /**
     * @param StreamInterface $spreadsheetStream
     * @return array
     * @throws InvalidInputException
     */
    public function getWorksheetData(StreamInterface $spreadsheetStream): array
    {
        $tempFileName = tempnam($this->tempFolder, 'validate_');
        $tempFile = fopen($tempFileName, 'w');

        fwrite($tempFile, $spreadsheetStream);

        $reader = new XlsReader();

        try {
            $spreadsheet = $reader->load($tempFileName);

            fclose($tempFile); // this removes the file
            unlink($tempFileName);

            $sheet = $spreadsheet->getActiveSheet();

            $data = $sheet->toArray();

            //First two rows are headers
            unset($data[0]);
            unset($data[1]);

            return $data;
        } catch (Exception $ex) {
            throw new InvalidInputException('Failed to parse uploaded spreadsheet');
        }
    }

    /**
     * @param string $fileFormat The file format of the resulting stream e.g. XLSX
     * @param ClaimSummary[] $claimSummaries
     * @param array $queryParameters
     * @return bool|resource a file pointer resource on success, or false on error.
     */
    public function getClaimSearchStream(string $fileFormat, array $claimSummaries, array $queryParameters)
    {
        $tempFileName = SpreadsheetFileNameFormatter::getTempFileName('Claim_Search', $fileFormat);
        $outputFilePath = $this->tempFolder . $tempFileName;
        if (file_exists($outputFilePath)) {
            throw new InvalidArgumentException('Temp file alrady exists');
        }

        $spreadsheet = new Spreadsheet();
        $resultsSheet = $spreadsheet->getActiveSheet();

        $resultsSheet->setCellValueByColumnAndRow(1, 1, 'Claim code');
        $resultsSheet->setCellValueByColumnAndRow(2, 1, 'Donor name');
        $resultsSheet->setCellValueByColumnAndRow(3, 1, 'Received');
        $resultsSheet->setCellValueByColumnAndRow(4, 1, 'Finished');
        $resultsSheet->setCellValueByColumnAndRow(5, 1, 'Assigned to/Finished by');
        $resultsSheet->setCellValueByColumnAndRow(6, 1, 'Status');

        $resultsRowIndex = 1;
        foreach ($claimSummaries as $claimSummary) {
            $resultsRowIndex++;

            $resultsSheet->setCellValueByColumnAndRow(1, $resultsRowIndex, $claimSummary->getReferenceNumber());
            $resultsSheet->setCellValueByColumnAndRow(2, $resultsRowIndex, $claimSummary->getDonorName());
            $this->setCellDateTime($resultsSheet, 'C' . $resultsRowIndex, $claimSummary->getReceivedDateTime());
            $this->setCellDateTime($resultsSheet, 'D' . $resultsRowIndex, $claimSummary->getFinishedDateTime());
            $resultsSheet->setCellValueByColumnAndRow(
                5,
                $resultsRowIndex,
                $claimSummary->getAssignedToName() ?: $claimSummary->getFinishedByName()
            );
            $resultsSheet->setCellValueByColumnAndRow(
                6,
                $resultsRowIndex,
                StatusFormatter::getStatusText($claimSummary->getStatus())
            );
        }

        $queryParametersSheet = $spreadsheet->createSheet();

        $queryParametersSheet->setCellValueByColumnAndRow(1, 1, 'Parameter');
        $queryParametersSheet->setCellValueByColumnAndRow(2, 1, 'Value');

        $searchParametersRowIndex = 1;
        foreach ($queryParameters as $parameter => $value) {
            $searchParametersRowIndex++;

            $queryParametersSheet->setCellValueByColumnAndRow(1, $searchParametersRowIndex, $parameter);
            $queryParametersSheet->setCellValueByColumnAndRow(2, $searchParametersRowIndex, $value);
        }

        $resultsSheet->setTitle('Results');

        //Set header bold
        $resultsSheet->getStyle('A1:F1')->getFont()->setBold(true);

        //Autofilter
        $resultsSheet->setAutoFilter($resultsSheet->calculateWorksheetDimension());

        //Auto width columns
        $resultsSheet->getColumnDimensionByColumn(1)->setAutoSize(true);
        $resultsSheet->getColumnDimensionByColumn(2)->setAutoSize(true);
        $resultsSheet->getColumnDimensionByColumn(3)->setAutoSize(true);
        $resultsSheet->getColumnDimensionByColumn(4)->setAutoSize(true);
        $resultsSheet->getColumnDimensionByColumn(5)->setAutoSize(true);
        $resultsSheet->getColumnDimensionByColumn(6)->setAutoSize(true);

        //Horizontal alignment
        $resultsSheet->getStyle("A1:F{$resultsRowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $queryParametersSheet->setTitle('Search parameters');

        //Set header bold
        $queryParametersSheet->getStyle('A1:B1')->getFont()->setBold(true);

        //Auto width columns
        $queryParametersSheet->getColumnDimensionByColumn(1)->setAutoSize(true);
        $queryParametersSheet->getColumnDimensionByColumn(2)->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);

        if ($fileFormat === ISpreadsheetGenerator::FILE_FORMAT_XLSX) {
            $writer = new XlsxWriter($spreadsheet);
            $writer->save($outputFilePath);
        } else {
            throw new InvalidArgumentException('Supplied schema and file format is not supported');
        }

        $handle = fopen($outputFilePath, 'r');

        return $handle;
    }

    private function setCellDateTime(Worksheet $worksheet, string $coordinate, $dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            // Set cell with the Excel date/time value
            $worksheet->setCellValue($coordinate, Date::PHPToExcel($dateTime));

            // Set the number format mask so that the excel timestamp will be displayed as a human-readable date/time
            $worksheet->getStyle($coordinate)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        }
    }

    /**
     * @param $start
     * @return float
     */
    private function getElapsedTimeInMs($start): float
    {
        return round((microtime(true) - $start) * 1000);
    }
}
