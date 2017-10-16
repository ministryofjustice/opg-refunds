<?php

namespace AppTest\Service\Refund;

use App\Service\RefundCalculator as RefundCalculatorService;
use DateTime;
use PHPUnit\Framework\TestCase;

class RefundTest extends TestCase
{
    /**
     * @var RefundCalculatorService
     */
    private $refundCalculatorService;

    protected function setUp()
    {
        $this->refundCalculatorService = new RefundCalculatorService();
    }

    public function testGetRefundAmount()
    {
        //As an example, say someone paid on 1/03/17 and their refund was processed on 1/05/17
        //They would be entitled to: Refund amount x daily interest rate x number of days
        //£45        x             0.00137%    x      61  = £0.04

        $originalPaymentAmount = 'orMore';
        $receivedDate = new DateTime('2017-03-01');

        $refundAmount = RefundCalculatorService::getRefundAmount($originalPaymentAmount, $receivedDate);

        //Test first thing in the morning
        $refundAmountWithInterest = $this->refundCalculatorService->getRefundInterestAmount(
            $originalPaymentAmount,
            $receivedDate,
            (new DateTime('2017-05-01T00:00:00.000000+0000'))->getTimestamp()
        );
        $interest = $refundAmountWithInterest - $refundAmount;

        $this->assertEquals(45.0, $refundAmount);
        $this->assertEquals(0.04, $interest);

        //Test last thing at night
        $refundAmountWithInterest = $this->refundCalculatorService->getRefundInterestAmount(
            $originalPaymentAmount,
            $receivedDate,
            (new DateTime('2017-05-01T23:59:59.999999+0000'))->getTimestamp()
        );
        $interest = $refundAmountWithInterest - $refundAmount;

        $this->assertEquals(45.0, $refundAmount);
        $this->assertEquals(0.04, $interest);
    }
}
