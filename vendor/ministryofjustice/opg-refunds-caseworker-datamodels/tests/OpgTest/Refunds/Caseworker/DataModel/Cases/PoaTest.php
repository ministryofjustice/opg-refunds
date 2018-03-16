<?php

namespace OpgTest\Refunds\Caseworker\DataModel\Applications;

use DateTime;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Applications\Postcodes;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification;
use OpgTest\Refunds\Caseworker\DataModel\AbstractDataModelTestCase;

class PoaTest extends AbstractDataModelTestCase
{
    public function testIsCompleteFalseEmptyCaseNumber()
    {
        $poa = (new Poa())
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $result = $poa->isComplete(new Claim());

        $this->assertFalse($result);
    }

    public function testIsCompleteFalseEmptyReceivedDate()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $result = $poa->isComplete(new Claim());

        $this->assertFalse($result);
    }

    public function testIsCompleteFalseEmptyOriginalPaymentAmount()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime());

        $result = $poa->isComplete(new Claim());

        $this->assertFalse($result);
    }

    public function testIsCompleteTrueClaimAttorneyValidationPoaNoAttorneyValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $result = $poa->isComplete((new Claim())->setPoas([
            (new Poa())->setVerifications([
                (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)->setPasses(true),
                (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)->setPasses(true)
            ])
        ])->setApplication(new Application()));

        $this->assertTrue($result);
    }

    public function testIsCompleteTrueClaimNoAttorneyValidationPoaAttorneyValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME),
            (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)
        ]);

        $result = $poa->isComplete((new Claim())->setApplication(new Application()));

        $this->assertTrue($result);
    }

    public function testIsCompleteFalsePartialAttorneyValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)
        ]);

        $result = $poa->isComplete(new Claim());

        $this->assertFalse($result);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)
        ]);

        $result = $poa->isComplete(new Claim());

        $this->assertFalse($result);
    }

    public function testIsCompleteFalsePartialAttorneyValidationClaimAttorneyValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)
        ]);

        $claim = (new Claim())->setPoas([
            (new Poa())->setVerifications([
                (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)->setPasses(true),
                (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)->setPasses(true)
            ])
        ]);

        $result = $poa->isComplete($claim);

        $this->assertFalse($result);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)
        ]);

        $result = $poa->isComplete($claim);

        $this->assertFalse($result);
    }

    public function testIsCompleteFalseNoDonorPostcodeValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $result = $poa->isComplete((new Claim())->setPoas([
            (new Poa())->setVerifications([
                (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)->setPasses(true),
                (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)->setPasses(true)
            ])
        ])->setApplication((new Application())->setPostcodes((new Postcodes())->setDonorPostcode('WS14 9UN'))));

        $this->assertFalse($result);
    }

    public function testIsCompleteTrueDonorPostcodeValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_DONOR_POSTCODE)
        ]);

        $result = $poa->isComplete((new Claim())->setPoas([
            (new Poa())->setVerifications([
                (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)->setPasses(true),
                (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)->setPasses(true)
            ])
        ])->setApplication((new Application())->setPostcodes((new Postcodes())->setDonorPostcode('WS14 9UN'))));

        $this->assertTrue($result);
    }

    public function testIsCompleteFalseNoAttorneyPostcodeValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $result = $poa->isComplete((new Claim())->setPoas([
            (new Poa())->setVerifications([
                (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)->setPasses(true),
                (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)->setPasses(true)
            ])
        ])->setApplication((new Application())->setPostcodes((new Postcodes())->setAttorneyPostcode('WS14 9UN'))));

        $this->assertFalse($result);
    }

    public function testIsCompleteTrueAttorneyPostcodeValidation()
    {
        $poa = (new Poa())
            ->setCaseNumber('1234567/1')
            ->setReceivedDate(new DateTime())
            ->setOriginalPaymentAmount(Poa::ORIGINAL_PAYMENT_AMOUNT_OR_MORE);

        $poa->setVerifications([
            (new Verification())->setType(Verification::TYPE_ATTORNEY_POSTCODE)
        ]);

        $result = $poa->isComplete((new Claim())->setPoas([
            (new Poa())->setVerifications([
                (new Verification())->setType(Verification::TYPE_ATTORNEY_NAME)->setPasses(true),
                (new Verification())->setType(Verification::TYPE_ATTORNEY_DOB)->setPasses(true)
            ])
        ])->setApplication((new Application())->setPostcodes((new Postcodes())->setAttorneyPostcode('WS14 9UN'))));

        $this->assertTrue($result);
    }
}