<?php

namespace AppTest\Service\Refund;

use App\Service\RefundCalculator as RefundCalculatorService;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
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

        $originalPaymentAmount = PoaModel::ORIGINAL_PAYMENT_AMOUNT_OR_MORE;
        $receivedDate = new DateTime('2017-03-01');

        $refundAmount = RefundCalculatorService::getRefundAmount($originalPaymentAmount, $receivedDate);

        //Test first thing in the morning
        $refundInterestAmount = $this->refundCalculatorService->getRefundInterestAmount(
            $originalPaymentAmount,
            $receivedDate,
            (new DateTime('2017-05-01T00:00:00.000000+0000'))->getTimestamp()
        );

        $this->assertEquals(45.0, $refundAmount);
        $this->assertEquals(0.04, $refundInterestAmount);

        //Test last thing at night
        $refundInterestAmount = $this->refundCalculatorService->getRefundInterestAmount(
            $originalPaymentAmount,
            $receivedDate,
            (new DateTime('2017-05-01T23:59:59.999999+0000'))->getTimestamp()
        );

        $this->assertEquals(45.0, $refundAmount);
        $this->assertEquals(0.04, $refundInterestAmount);
    }
}
