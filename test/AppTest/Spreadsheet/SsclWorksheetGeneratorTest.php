<?php

namespace AppTest\Spreadsheet;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use Opg\Refunds\Caseworker\DataModel\Applications\CurrentWithAddress;
use Opg\Refunds\Caseworker\DataModel\Applications\Donor;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;
use App\Spreadsheet\ISpreadsheetWorksheetGenerator;
use App\Spreadsheet\SpreadsheetRow;
use App\Spreadsheet\SsclWorksheetGenerator;
use AppTest\DataModel\Applications\ApplicationBuilder;
use AppTest\DataModel\Cases\ClaimBuilder;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\User;
use Opg\Refunds\Caseworker\DataModel\Common\Address;
use Opg\Refunds\Caseworker\DataModel\Common\Name;
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
        'objective' => '00000000',
        'analysis' => '12345678',
        'completer_id' => '',
        'approver_id' => '',
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

    /**
     * @var User
     */
    private $approver;

    public function setUp()
    {
        $this->generator = new SsclWorksheetGenerator($this->ssclConfig);
        $this->claimBuilder = new ClaimBuilder();
        $this->applicationBuilder = new ApplicationBuilder();

        $donor = new Donor();
        $name = new Name();
        $name->setTitle('Ms')
             ->setFirst('Test')
             ->setLast('Donor');
        $address = new Address();
        $address->setAddress1('10 Test Road')
                ->setAddress2('Testington')
                ->setAddressPostcode('TS1 1ON');
        $current = new CurrentWithAddress();
        $current->setName($name);
        $current->setAddress($address);
        $donor->setCurrent($current);

        $account = new Account();
        $account
            ->setName('Mr Unit Test')
            ->setAccountNumber('12345678')
            ->setSortCode('112233');

        $application = $this->applicationBuilder->withDonor($donor)->withAccount($account)->build();

        $payment = new Payment();
        $payment->setAmount(45);

        $this->claim = $this->claimBuilder
            ->withApplication($application)
            ->withPayment($payment)
            ->withFinishedByName('Test FinishedBy')
            ->build();

        $this->approver = new User();
        $this->approver->setName('Test Approver');
    }

    public function testEmptyArray()
    {
        $result = $this->generator->generate(new DateTime(), [], $this->approver);

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

        $result = $this->generator->generate(new DateTime(), $claims, $this->approver);

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
            $this->assertEquals('Test', $cells[3]->getData());

            //Payee Name
            $this->assertEquals(6, $cells[4]->getColumn());
            $this->assertEquals('Donor', $cells[4]->getData());

            //Payee Address (use commas to separate)
            $this->assertEquals(7, $cells[5]->getColumn());
            $this->assertEquals('10 Test Road', $cells[5]->getData());

            //Payee Address (use commas to separate)
            $this->assertEquals(8, $cells[6]->getColumn());
            $this->assertEquals('', $cells[6]->getData());

            //Payee Address (use commas to separate)
            $this->assertEquals(9, $cells[7]->getColumn());
            $this->assertEquals('Testington', $cells[7]->getData());

            //Payee Postcode
            $this->assertEquals(10, $cells[8]->getColumn());
            $this->assertEquals('TS1 1ON', $cells[8]->getData());

            //Payment Method
            $this->assertEquals(13, $cells[9]->getColumn());
            $this->assertEquals('New Bank Details', $cells[9]->getData());

            //Sort Code
            $this->assertEquals(14, $cells[10]->getColumn());
            $this->assertEquals($account->getSortCode(), $cells[10]->getData());

            //Account Number
            $this->assertEquals(15, $cells[11]->getColumn());
            $this->assertEquals($account->getAccountNumber(), $cells[11]->getData());

            //Name of Bank
            $this->assertEquals(16, $cells[12]->getColumn());
            $this->assertEquals('Halifax', $cells[12]->getData());

            //Account Name
            $this->assertEquals(17, $cells[13]->getColumn());
            $this->assertEquals($account->getName(), $cells[13]->getData());

            //Roll Number - Should be blank rather than N/A - SSCL confirmed on 10/11/2017
            $this->assertEquals(18, $cells[14]->getColumn());
            $this->assertEquals('', $cells[14]->getData());

            //Invoice Date
            $this->assertEquals(19, $cells[15]->getColumn());
            $this->assertEquals((new DateTime('today'))->format('d/m/Y'), $cells[15]->getData());

            //Invoice Number - Programme board instructed to use reference number on 02/11/2017
            $this->assertEquals(20, $cells[16]->getColumn());
            $this->assertEquals($claim->getReferenceNumber(), $cells[16]->getData());

            //Description
            $this->assertEquals(21, $cells[17]->getColumn());
            $this->assertEquals('Lasting Power of Attorney', $cells[17]->getData());

            //Entity - From config
            $this->assertEquals(22, $cells[18]->getColumn());
            $this->assertEquals('0123', $cells[18]->getData());

            //Cost Centre - From config
            $this->assertEquals(23, $cells[19]->getColumn());
            $this->assertEquals('99999999', $cells[19]->getData());

            //Account - From config
            $this->assertEquals(24, $cells[20]->getColumn());
            $this->assertEquals('123450000', $cells[20]->getData());

            //Objective - From config
            $this->assertEquals(25, $cells[21]->getColumn());
            $this->assertEquals('0', $cells[21]->getData());

            //Analysis - From config
            $this->assertEquals(26, $cells[22]->getColumn());
            $this->assertEquals('12345678', $cells[22]->getData());

            //VAT Rate
            $this->assertEquals(27, $cells[23]->getColumn());
            $this->assertEquals('UK OUT OF SCOPE', $cells[23]->getData());

            //Net Amount
            $this->assertEquals(29, $cells[24]->getColumn());
            $this->assertEquals($payment->getAmount(), $cells[24]->getData());

            //VAT Amount
            $this->assertEquals(30, $cells[25]->getColumn());
            $this->assertEquals(0, $cells[25]->getData());

            //Total Amount
            $this->assertEquals(31, $cells[26]->getColumn());
            $this->assertEquals($payment->getAmount(), $cells[26]->getData());

            //Completer ID - From config
            $this->assertEquals(32, $cells[27]->getColumn());
            $this->assertEquals($this->claim->getFinishedByName(), $cells[27]->getData());

            //Approver ID - From config
            $this->assertEquals(33, $cells[28]->getColumn());
            $this->assertEquals($this->approver->getName(), $cells[28]->getData());
        }
    }
}
