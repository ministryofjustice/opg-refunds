<?php

namespace AppTest\Service\Date;

use App\Service\Date\IDateProvider;
use App\Service\Poa\PoaFormatter;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa;
use PHPUnit\Framework\TestCase;

class PoaFormatterTest extends TestCase
{
    /**
     * @var MockInterface|IDateProvider
     */
    private $dateProvider;
    /**
     * @var PoaFormatter
     */
    private $poaFormatter;

    protected function setUp()
    {
        $this->dateProvider = Mockery::mock(IDateProvider::class);
        $this->poaFormatter = new PoaFormatter($this->dateProvider);
    }

    public function testGetRefundAmount()
    {
        //As an example, say someone paid on 1/03/17 and their refund was processed on 1/05/17.  They would be entitled to:
        //Refund amount x daily interest rate x number of days
        //£45        x             0.00137%    x      61  = £0.04

        $poa = new Poa();
        $poa->setReceivedDate(new DateTime('2017-03-01'))
            ->setOriginalPaymentAmount('orMore');

        $refundAmount = $this->poaFormatter->getRefundAmount($poa);

        //Test first thing in the morning
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-05-01T00:00:00.000000+0000'))->getTimestamp());
        $refundAmountWithInterest = $this->poaFormatter->getAmountWithInterest($poa, $refundAmount);
        $interest = $refundAmountWithInterest - $refundAmount;

        $this->assertEquals(45.0, $refundAmount);
        $this->assertEquals(0.04, $interest);

        //Test last thing at night
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-05-01T23:59:59.999999+0000'))->getTimestamp());
        $refundAmountWithInterest = $this->poaFormatter->getAmountWithInterest($poa, $refundAmount);
        $interest = $refundAmountWithInterest - $refundAmount;

        $this->assertEquals(45.0, $refundAmount);
        $this->assertEquals(0.04, $interest);
    }
}