<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\PhpSpreadsheetGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhpSpreadsheetGeneratorTest extends TestCase
{
    /**
     * @var ISpreadsheetGenerator
     */
    private $spreadsheetGenerator;

    public function setUp()
    {
        $this->spreadsheetGenerator = new PhpSpreadsheetGenerator();
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Supplied schema and file format is not supported
     */
    public function testSchemaFileFormatNotSupported()
    {
        $this->spreadsheetGenerator->generate(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            []
        );
    }

    public function testSchemaSsclFileFormatXlsOneRow()
    {
        $result = $this->spreadsheetGenerator->generate(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS,
            [
                [
                    'payeeName' => 'Mr Unit Test',
                    'accountNumber' => '12345678',
                    'sortCode' => '112233',
                    'amount' => 45,
                    'reference' => 'AREFERENCE123'
                ]
            ]
        );

        $this->assertNotNull($result);
    }
}