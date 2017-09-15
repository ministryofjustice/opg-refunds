<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;
use DateTime;

class RefundCaseTest extends AbstractDataModelTestCase
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
        $model = new RefundCase();

        $now = new DateTime();

        $model->setId(12345678)
              ->setReferenceNumber('REF-1234')
              ->setCreatedDateTime($now)
              ->setUpdatedDateTime($now)
              ->setReceivedDateTime($now)
              ->setApplication($this->application)
              ->setStatus(RefundCase::STATUS_NEW)
              ->setAssignedToId(123)
              ->setAssignedDateTime($now)
              ->setFinishedDateTime($now)
              ->setDonorName('Joey Tribbiani')
              ->setPayment($this->payment);

        $this->assertEquals(12345678, $model->getId());
        $this->assertEquals('REF-1234', $model->getReferenceNumber());
        $this->assertEquals($now, $model->getCreatedDateTime());
        $this->assertEquals($now, $model->getUpdatedDateTime());
        $this->assertEquals($now, $model->getReceivedDateTime());
        $this->assertEquals($this->application, $model->getApplication());
        $this->assertEquals(RefundCase::STATUS_NEW, $model->getStatus());
        $this->assertEquals(123, $model->getAssignedToId());
        $this->assertEquals($now, $model->getAssignedDateTime());
        $this->assertEquals($now, $model->getFinishedDateTime());
        $this->assertEquals('Joey Tribbiani', $model->getDonorName());
        $this->assertEquals($this->payment, $model->getPayment());
    }

    public function testPopulateAndToArray()
    {
        $now = new DateTime();

        $data = [
            'id'                 => 12345678,
            'reference-number'   => 'REF-1234',
            'created-date-time'  => $this->dateTimeToString($now),
            'updated-date-time'  => $this->dateTimeToString($now),
            'received-date-time' => $this->dateTimeToString($now),
            'application'        => $this->application->toArray(),
            'status'             => RefundCase::STATUS_NEW,
            'assigned-to-id'     => 123,
            'assigned-date-time' => $this->dateTimeToString($now),
            'finished-date-time' => $this->dateTimeToString($now),
            'donor-name'         => 'Joey Tribbiani',
            'payment'            => $this->payment->toArray(),
        ];

        $model = new RefundCase($data);

        $this->assertSame($data, $model->toArray());
    }
}
