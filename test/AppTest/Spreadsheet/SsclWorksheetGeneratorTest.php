<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetRow;
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

    public function testEmptyArray()
    {
        $result = $this->generator->generate([]);

        $this->assertNotNull($result);
        $this->assertEquals('Data', $result->getName());

        /** @var SpreadsheetRow[] $rows */
        $rows = $result->getRows();
        $this->assertNotNull($rows);
        $this->assertEquals(0, count($rows));
    }

    public function testSingleRow()
    {
        $data = [
            [
                'payeeName' => 'Mr Unit Test',
                'accountNumber' => '12345678',
                'sortCode' => '112233',
                'amount' => 45,
                'reference' => 'AREFERENCE123'
            ]
        ];

        $result = $this->generator->generate($data);

        $this->assertNotNull($result);
        $this->assertEquals('Data', $result->getName());

        /** @var SpreadsheetRow[] $rows */
        $rows = $result->getRows();
        $this->assertNotNull($rows);
        $this->assertEquals(1, count($rows));

        foreach ($rows as $idx => $row) {
            $cells = $row->getCells();
            $datum = $data[$idx];

            //Verify all cells have the same, valid row number
            foreach ($cells as $cell) {
                $this->assertEquals(3, $cell->getRow());
            }

            //Verify each cell contains the correct column and data
            //reference
            $this->assertEquals(4, $cells[0]->getColumn());
            $this->assertEquals($datum['reference'], $cells[0]->getData());

            //payeeName
            $this->assertEquals(5, $cells[1]->getColumn());
            $this->assertEquals($datum['payeeName'], $cells[1]->getData());

            //sortCode
            $this->assertEquals(11, $cells[2]->getColumn());
            $this->assertEquals($datum['sortCode'], $cells[2]->getData());

            //accountNumber
            $this->assertEquals(12, $cells[3]->getColumn());
            $this->assertEquals($datum['accountNumber'], $cells[3]->getData());

            //amount
            $this->assertEquals(26, $cells[4]->getColumn());
            $this->assertEquals($datum['amount'], $cells[4]->getData());

            //amount
            $this->assertEquals(28, $cells[5]->getColumn());
            $this->assertEquals($datum['amount'], $cells[5]->getData());
        }
    }
}
