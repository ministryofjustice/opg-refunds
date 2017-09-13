<?php

namespace AppTest\Spreadsheet;

use App\DataModel\Applications\Account;
use App\DataModel\Cases\Payment;
use App\DataModel\Cases\RefundCase as CaseDataModel;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetRow;
use App\Spreadsheet\SsclWorksheetGenerator;
use AppTest\DataModel\Applications\ApplicationBuilder;
use AppTest\DataModel\Cases\RefundCaseBuilder;
use PHPUnit\Framework\TestCase;

class SsclWorksheetGeneratorTest extends TestCase
{
    /**
     * @var ISpreadsheetWorksheetGenerator
     */
    private $generator;

    /**
     * @var RefundCaseBuilder
     */
    private $refundCaseBuilder;

    /**
     * @var ApplicationBuilder
     */
    private $applicationBuilder;

    /**
     * @var CaseDataModel
     */
    private $case;

    public function setUp()
    {
        $this->generator = new SsclWorksheetGenerator();
        $this->refundCaseBuilder = new RefundCaseBuilder();
        $this->applicationBuilder = new ApplicationBuilder();

        $account = new Account();
        $account->setName('Mr Unit Test');
        $account->setAccountNumber('12345678');
        $account->setSortCode('112233');

        $application = $this->applicationBuilder->withAccount($account)->build();

        $payment = new Payment();
        $payment->setAmount(45);

        $this->case = $this->refundCaseBuilder
            ->withApplication($application)
            ->withPayment($payment)
            ->build();
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

    public function testSingleCase()
    {
        /** @var CaseDataModel[] $cases */
        $cases = [
            $this->case
        ];

        $result = $this->generator->generate($cases);

        $this->assertNotNull($result);
        $this->assertEquals('Data', $result->getName());

        /** @var SpreadsheetRow[] $rows */
        $rows = $result->getRows();
        $this->assertNotNull($rows);
        $this->assertEquals(1, count($rows));

        foreach ($rows as $idx => $row) {
            $cells = $row->getCells();

            /** @var CaseDataModel $case */
            $case = $cases[$idx];
            $account = $case->getApplication()->getAccount();
            $payment = $case->getPayment();

            //Verify all cells have the same, valid row number
            foreach ($cells as $cell) {
                $this->assertEquals(3, $cell->getRow());
            }

            //Verify each cell contains the correct column and data
            //reference
            $this->assertEquals(4, $cells[0]->getColumn());
            $this->assertEquals($case->getId(), $cells[0]->getData());

            //payeeName
            $this->assertEquals(5, $cells[1]->getColumn());
            $this->assertEquals($account->getName(), $cells[1]->getData());

            //sortCode
            $this->assertEquals(11, $cells[2]->getColumn());
            $this->assertEquals($account->getSortCode(), $cells[2]->getData());

            //accountNumber
            $this->assertEquals(12, $cells[3]->getColumn());
            $this->assertEquals($account->getAccountNumber(), $cells[3]->getData());

            //amount
            $this->assertEquals(26, $cells[4]->getColumn());
            $this->assertEquals($payment->getAmount(), $cells[4]->getData());

            //amount
            $this->assertEquals(28, $cells[5]->getColumn());
            $this->assertEquals($payment->getAmount(), $cells[5]->getData());
        }
    }
}
