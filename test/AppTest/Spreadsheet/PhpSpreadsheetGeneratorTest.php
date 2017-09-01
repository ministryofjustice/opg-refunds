<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use App\Spreadsheet\SpreadsheetWorksheet;
use App\Spreadsheet\SsclWorksheetGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhpSpreadsheetGeneratorTest extends TestCase
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
        $this->spreadsheetGenerator = new PhpSpreadsheetGenerator($this->sourceFolder, $this->tempFolder);

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

    public function tearDown()
    {
        foreach ($this->filesToDelete as $fileToDelete) {
            unlink($fileToDelete);
        }
    }
}