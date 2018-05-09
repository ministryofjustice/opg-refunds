<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;
use DateTime;

class ClaimTest extends AbstractDataModelTestCase
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
        $model = new Claim();

        $now = new DateTime();

        $model->setId(12345678)
              ->setCreatedDateTime($now)
              ->setUpdatedDateTime($now)
              ->setReceivedDateTime($now)
              ->setApplication($this->application)
              ->setStatus(Claim::STATUS_PENDING)
              ->setAssignedToId(123)
              ->setAssignedDateTime($now)
              ->setFinishedDateTime($now)
              ->setDonorName('Joey Tribbiani')
              ->setPayment($this->payment);

        $this->assertEquals(12345678, $model->getId());
        $this->assertEquals('R000 1234 5678', $model->getReferenceNumber());
        $this->assertEquals($now, $model->getCreatedDateTime());
        $this->assertEquals($now, $model->getUpdatedDateTime());
        $this->assertEquals($now, $model->getReceivedDateTime());
        $this->assertEquals($this->application, $model->getApplication());
        $this->assertEquals(Claim::STATUS_PENDING, $model->getStatus());
        $this->assertEquals(123, $model->getAssignedToId());
        $this->assertEquals($now, $model->getAssignedDateTime());
        $this->assertEquals($now, $model->getFinishedDateTime());
        $this->assertEquals('Joey Tribbiani', $model->getDonorName());
        $this->assertEquals($this->payment, $model->getPayment());
    }

    public function testPopulateAndGetArrayCopy()
    {
        $now = new DateTime();

        $data = [
            'id'                 => 12345678,
            'reference-number'   => 'R000 1234 5678',
            'created-date-time'  => $this->dateTimeToString($now),
            'updated-date-time'  => $this->dateTimeToString($now),
            'received-date-time' => $this->dateTimeToString($now),
            'application'        => $this->application->getArrayCopy(),
            'status'             => Claim::STATUS_PENDING,
            'assigned-to-id'     => 123,
            'assigned-date-time' => $this->dateTimeToString($now),
            'finished-date-time' => $this->dateTimeToString($now),
            'donor-name'         => 'Joey Tribbiani',
            'payment'            => $this->payment->getArrayCopy(),
        ];

        $model = new Claim($data);

        $this->assertSame($data, $model->getArrayCopy());
    }
}
