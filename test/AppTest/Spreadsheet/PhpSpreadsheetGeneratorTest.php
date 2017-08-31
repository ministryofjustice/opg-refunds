<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use App\Spreadsheet\SpreadsheetWorksheet;
use App\Spreadsheet\SsclWorksheetGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhpSpreadsheetGeneratorTest extends TestCase
{
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

    public function setUp()
    {
        $this->spreadsheetGenerator = new PhpSpreadsheetGenerator();

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
        $this->filesToDelete[] = $result;

        $this->assertNotNull($result);
        $this->assertEquals(PhpSpreadsheetGenerator::SPREADSHEET_OUTPUT_FOLDER . 'UnitTest.xls', $result);
        $this->assertTrue(file_exists($result));
    }

    public function tearDown()
    {
        foreach ($this->filesToDelete as $fileToDelete) {
            unlink($fileToDelete);
        }
    }
}