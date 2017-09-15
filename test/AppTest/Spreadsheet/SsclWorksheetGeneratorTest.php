<?php

namespace AppTest\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase as CaseDataModel;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetRow;
use App\Spreadsheet\SsclWorksheetGenerator;
use AppTest\DataModel\Applications\ApplicationBuilder;
use AppTest\DataModel\Cases\RefundCaseBuilder;
use DateTime;
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
        $account
            ->setName('Mr Unit Test')
            ->setAccountNumber('12345678')
            ->setSortCode('112233');

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
            //OU
            $this->assertEquals(0, $cells[0]->getColumn());
            $this->assertEquals('MOJ', $cells[0]->getData());

            //Payee Type
            $this->assertEquals(3, $cells[1]->getColumn());
            $this->assertEquals('Client', $cells[1]->getData());

            //Unique Payee Reference
            $this->assertEquals(4, $cells[2]->getColumn());
            $this->assertEquals($case->getReferenceNumber(), $cells[2]->getData());

            //Payee Name
            $this->assertEquals(5, $cells[3]->getColumn());
            $this->assertEquals($account->getName(), $cells[3]->getData());

            //Payee Address (use commas to separate)
            $this->assertEquals(6, $cells[4]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[4]->getData());

            //Payee Postcode
            $this->assertEquals(7, $cells[5]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[5]->getData());

            //Payment Method
            $this->assertEquals(10, $cells[6]->getColumn());
            $this->assertEquals('New Bank Details', $cells[6]->getData());

            //Sort Code
            $this->assertEquals(11, $cells[7]->getColumn());
            $this->assertEquals($account->getSortCode(), $cells[7]->getData());

            //Account Number
            $this->assertEquals(12, $cells[8]->getColumn());
            $this->assertEquals($account->getAccountNumber(), $cells[8]->getData());

            //Name of Bank
            $this->assertEquals(13, $cells[9]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[9]->getData());

            //Account Name
            $this->assertEquals(14, $cells[10]->getColumn());
            $this->assertEquals($account->getName(), $cells[10]->getData());

            //Roll Number
            $this->assertEquals(15, $cells[11]->getColumn());
            $this->assertEquals('N/A', $cells[11]->getData());

            //Invoice Date
            $this->assertEquals(16, $cells[12]->getColumn());
            $this->assertEquals((new DateTime('today'))->format('d/m/Y'), $cells[12]->getData());

            //Invoice Number
            $this->assertEquals(17, $cells[13]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[13]->getData());

            //Description
            $this->assertEquals(18, $cells[14]->getColumn());
            $this->assertEquals('Lasting Power of Attorney', $cells[14]->getData());

            //Entity
            $this->assertEquals(19, $cells[15]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[15]->getData());

            //Cost Centre
            $this->assertEquals(20, $cells[16]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[16]->getData());

            //Account
            $this->assertEquals(21, $cells[17]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[17]->getData());

            //Objective
            $this->assertEquals(22, $cells[18]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[18]->getData());

            //Analysis
            $this->assertEquals(23, $cells[19]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[19]->getData());

            //VAT Rate
            $this->assertEquals(24, $cells[20]->getColumn());
            $this->assertEquals('UK OUT OF SCOPE', $cells[20]->getData());

            //Net Amount
            $this->assertEquals(26, $cells[21]->getColumn());
            $this->assertEquals($payment->getAmount(), $cells[21]->getData());

            //VAT Amount
            $this->assertEquals(27, $cells[22]->getColumn());
            $this->assertEquals(0, $cells[22]->getData());

            //Total Amount
            $this->assertEquals(28, $cells[23]->getColumn());
            $this->assertEquals($payment->getAmount(), $cells[23]->getData());

            //Completer ID
            $this->assertEquals(29, $cells[24]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[24]->getData());

            //Approver ID
            $this->assertEquals(30, $cells[25]->getColumn());
            $this->assertEquals('UNDEFINED', $cells[25]->getData());
        }
    }
}
