<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SsclWorksheetGenerator;
use PHPUnit\Framework\TestCase;

class SsclWorksheetGeneratorTest extends TestCase
{
    /**
     * @var ISpreadsheetWorksheetGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->generator = new SsclWorksheetGenerator();
    }
}