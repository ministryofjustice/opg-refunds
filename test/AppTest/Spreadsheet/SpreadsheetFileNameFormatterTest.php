<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetGenerator;
use App\Spreadsheet\SpreadsheetFileNameFormatter;
use PHPUnit\Framework\TestCase;

class SpreadsheetFileNameFormatterTest extends TestCase
{
    /**
     * @var SpreadsheetFileNameFormatter
     */
    private $formatter;

    public function setUp()
    {
        $this->formatter = new SpreadsheetFileNameFormatter();
    }

    public function testSsclXlsFileName()
    {
        $result = $this->formatter->getFileName(
            ISpreadsheetGenerator::SCHEMA_SSCL,
            ISpreadsheetGenerator::FILE_FORMAT_XLS
        );

        $this->assertEquals('OPG Multi-SOP1 Refund Requests.xls', $result);
    }
}