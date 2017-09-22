<?php

namespace AppTest\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetRow;
use App\Spreadsheet\SsclWorksheetGenerator;
use AppTest\DataModel\Applications\ApplicationBuilder;
use AppTest\DataModel\Cases\ClaimBuilder;
use DateTime;
use PHPUnit\Framework\TestCase;

class SsclWorksheetGeneratorTest extends TestCase
{
    /**
     * @var array
     */
    private $ssclConfig = [
        'entity' => '0123',
        'cost_centre' => '99999999',
        'account' => '123450000',
        'objective' => '0',
        'analysis' => '12345678',
        'completer_id' => 'completer@localhost.com',
        'approver_id' => 'approver@localhost.com',
    ];
    /**
     * @var ISpreadsheetWorksheetGenerator
     */
    private $generator;

    /**
     * @var ClaimBuilder
     */
    private $claimBuilder;

    /**
     * @var ApplicationBuilder
     */
    private $applicationBuilder;

    /**
     * @var Claim
     */
    private $claim;

    public function setUp()
    {
        $this->generator = new SsclWorksheetGenerator($this->ssclConfig);
        $this->claimBuilder = new ClaimBuilder();
        $this->applicationBuilder = new ApplicationBuilder();

        $account = new Account();
        $account
            ->setName('Mr Unit Test')
            ->setAccountNumber('12345678')
            ->setSortCode('112233');

        $application = $this->applicationBuilder->withAccount($account)->build();

        $payment = new Payment();
        $payment->setAmount(45);

        $this->claim = $this->claimBuilder
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

    public function testSingleClaim()
    {
        /** @var Claim[] $claims */
        $claims = [
            $this->claim
        ];

        $result = $this->generator->generate($claims);

        $this->assertNotNull($result);
        $this->assertEquals('Data', $result->getName());

        /** @var SpreadsheetRow[] $rows */
        $rows = $result->getRows();
        $this->assertNotNull($rows);
        $this->assertEquals(1, count($rows));

        foreach ($rows as $idx => $row) {
            $cells = $row->getCells();

            /** @var Claim $claim */
            $claim = $claims[$idx];
            $account = $claim->getApplication()->getAccount();
            $payment = $claim->getPayment();

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
            $this->assertEquals($claim->getReferenceNumber(), $cells[2]->getData());

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

            //Name of Bank - Not required by SSCL (Georgia confirmed on 21/09/2017)
            $this->assertEquals(13, $cells[9]->getColumn());
            $this->assertEquals('', $cells[9]->getData());

            //Account Name
            $this->assertEquals(14, $cells[10]->getColumn());
            $this->assertEquals($account->getName(), $cells[10]->getData());

            //Roll Number
            $this->assertEquals(15, $cells[11]->getColumn());
            $this->assertEquals('N/A', $cells[11]->getData());

            //Invoice Date
            $this->assertEquals(16, $cells[12]->getColumn());
            $this->assertEquals((new DateTime('today'))->format('d/m/Y'), $cells[12]->getData());

            //Invoice Number - Not required by SSCL (Georgia confirmed on 21/09/2017)
            $this->assertEquals(17, $cells[13]->getColumn());
            $this->assertEquals('', $cells[13]->getData());

            //Description
            $this->assertEquals(18, $cells[14]->getColumn());
            $this->assertEquals('Lasting Power of Attorney', $cells[14]->getData());

            //Entity - From config
            $this->assertEquals(19, $cells[15]->getColumn());
            $this->assertEquals('0123', $cells[15]->getData());

            //Cost Centre - From config
            $this->assertEquals(20, $cells[16]->getColumn());
            $this->assertEquals('99999999', $cells[16]->getData());

            //Account - From config
            $this->assertEquals(21, $cells[17]->getColumn());
            $this->assertEquals('123450000', $cells[17]->getData());

            //Objective - From config
            $this->assertEquals(22, $cells[18]->getColumn());
            $this->assertEquals('0', $cells[18]->getData());

            //Analysis - From config
            $this->assertEquals(23, $cells[19]->getColumn());
            $this->assertEquals('12345678', $cells[19]->getData());

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

            //Completer ID - From config
            $this->assertEquals(29, $cells[24]->getColumn());
            $this->assertEquals('completer@localhost.com', $cells[24]->getData());

            //Approver ID - From config
            $this->assertEquals(30, $cells[25]->getColumn());
            $this->assertEquals('approver@localhost.com', $cells[25]->getData());
        }
    }
}
