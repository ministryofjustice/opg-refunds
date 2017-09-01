<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\PhpExcelGenerator;
use App\Spreadsheet\SpreadsheetWorksheet;
use App\Spreadsheet\SsclWorksheetGenerator;
use InvalidArgumentException;
use PHPExcel_Reader_Excel5 as XlsReader;
use PHPUnit\Framework\TestCase;

class PhpExcelGeneratorTest extends TestCase
{
    private $sourceFolder = __DIR__ . '/../../../assets';
    private $tempFolder = __DIR__ . '/../output';
    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;
    /**
     * @var SpreadsheetWorksheet
     */
    private $worksheet;
    /**
     * @var array
     */
    private $filesToDelete = [];

    public static function setUpBeforeClass()
    {
        $filename = __DIR__ . '/../output/UnitTest.xls';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function setUp()
    {
        $this->spreadsheetGenerator = new PhpExcelGenerator($this->sourceFolder, $this->tempFolder);

        $data = [
            [
                'payeeName' => 'Mr Unit Test',
                'accountNumber' => '12345678',
                'sortCode' => '112233',
                'amount' => 45,
                'reference' => 'AREFERENCE123'
            ]
        ];

        $spreadsheetWorksheetGenerator = new SsclWorksheetGenerator();
        $this->worksheet = $spreadsheetWorksheetGenerator->generate($data);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Supplied schema and file format is not supported
     */
    public function testSchemaFileFormatNotSupported()
    {
        $this->spreadsheetGenerator->generateFile(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLSX,
            'UnitTest.xls',
            $this->worksheet
        );
    }

    public function testSchemaSsclFileFormatXlsOneRow()
    {
        $result = $this->spreadsheetGenerator->generateFile(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            'UnitTest.xls',
            $this->worksheet
        );

        $this->assertNotNull($result);
        $this->assertEquals($this->tempFolder . '/UnitTest.xls', $result);
        $this->assertTrue(file_exists($result));

        //Load and check spreadsheet data
        $reader = new XlsReader();
        $ssclSourceSpreadsheet = $reader->load($result);

        $dataSheet = $ssclSourceSpreadsheet->getSheetByName($this->worksheet->getName());

        foreach ($this->worksheet->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                $this->assertEquals(
                    $cell->getData(),
                    $dataSheet->getCellByColumnAndRow(
                        $cell->getColumn(),
                        $cell->getRow()
                    )->getValue()
                );
            }
        }
    }

    /**
     * @depends testSchemaSsclFileFormatXlsOneRow
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Supplied filename already exists in temp folder
     */
    public function testFileAlreadyExists()
    {
        $this->spreadsheetGenerator->generateFile(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            'UnitTest.xls',
            $this->worksheet
        );
    }

    /**
     * @depends testFileAlreadyExists
     */
    public function testDeleteTempFiles()
    {
        $this->assertTrue(file_exists($this->tempFolder . '/UnitTest.xls'));

        $this->spreadsheetGenerator->deleteTempFiles();

        $this->assertFalse(file_exists($this->tempFolder . '/UnitTest.xls'));
    }

    /**
     * @depends testDeleteTempFiles
     */
    public function testGenerateStream()
    {
        $result = $this->spreadsheetGenerator->generateStream(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            $this->worksheet
        );

        $this->assertNotNull($result);
        $this->assertNotFalse($result);

        //Save stream to file for testing
        $fileName = $this->tempFolder . '/StreamUnitTest.xls';
        file_put_contents($fileName, $result);

        $this->assertTrue(file_exists($fileName));

        //Load and check spreadsheet data
        $reader = new XlsReader();
        $ssclSourceSpreadsheet = $reader->load($fileName);

        $dataSheet = $ssclSourceSpreadsheet->getSheetByName($this->worksheet->getName());

        foreach ($this->worksheet->getRows() as $row) {
            foreach ($row->getCells() as $cell) {
                $this->assertEquals(
                    $cell->getData(),
                    $dataSheet->getCellByColumnAndRow(
                        $cell->getColumn(),
                        $cell->getRow()
                    )->getValue()
                );
            }
        }

        $this->spreadsheetGenerator->deleteTempFiles();
    }

    public function tearDown()
    {
        foreach ($this->filesToDelete as $fileToDelete) {
            unlink($fileToDelete);
        }
    }
}