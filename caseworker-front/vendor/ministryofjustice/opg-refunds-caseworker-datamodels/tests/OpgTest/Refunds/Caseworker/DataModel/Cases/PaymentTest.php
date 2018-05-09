<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;
use DateTime;

class PaymentTest extends AbstractDataModelTestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Payment
     */
    private $payment;

    public function setUp()
    {
        //  Set up the data entities to reuse
        $this->application = new Application([
            'donor' => [
                'name' => [
                    'title' => 'Dr',
                    'first' => 'Ross',
                    'last'  => 'Gellar',
                ],
                'dob'  => $this->dateTimeToString(new DateTime('1966-11-02')),
            ],
            'attorney' => [
                'name' => [
                    'title' => 'Miss',
                    'first' => 'Monica',
                    'last'  => 'Gellar',
                ],
                'dob'  => $this->dateTimeToString(new DateTime('1964-06-15')),
            ],
            'contact' => [
                'email'  => 'ross@friends.com',
                'mobile' => '07712 123456',
            ],
            'verification' => [
                'case-number'       => '123456789',
                'donor-postcode'    => 'AB1 2CD',
                'attorney-postcode' => 'WX9 8YZ',
            ],
            'account' => [
                'name' => 'DR R GELLAR',
            ],
        ]);

        $this->payment = new Payment([
            'amount'              => '',
            'method'              => '',
            'added-date-time'     => $this->dateTimeToString(new DateTime()),
            'processed-date-time' => $this->dateTimeToString(new DateTime()),
        ]);
    }

    public function testGetsAndSets()
    {
        $model = new Payment();

        $now = new DateTime();

        $model->setAmount(54.32)
              ->setMethod('CHEQUE')
              ->setAddedDateTime($now)
              ->setProcessedDateTime($now);

        $this->assertEquals(54.32, $model->getAmount());
        $this->assertEquals('CHEQUE', $model->getMethod());
        $this->assertEquals($now, $model->getAddedDateTime());
        $this->assertEquals($now, $model->getProcessedDateTime());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $now = new DateTime();

        $data = [
            'amount'              => 54.32,
            'method'              => 'CHEQUE',
            'added-date-time'     => $this->dateTimeToString($now),
            'processed-date-time' => $this->dateTimeToString($now),
        ];

        $model = new Payment($data);

        $this->assertSame($data, $model->getArrayCopy());
    }
}
