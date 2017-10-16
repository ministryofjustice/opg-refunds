<?php

namespace App\Service;

use App\Service\TimeDate as TimeDateService;
use DateTime;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;

/**
 * Class RefundCalculator
 * @package App\Service\RefundCalculator
 */
class RefundCalculator
{
    /**
     * @var TimeDateService
     */
    private $timeDateService;

    public function __construct(TimeDateService $timeDateService)
    {
        $this->timeDateService = $timeDateService;
    }

    public function getRefundAmount(PoaModel $poa)
    {
        //TODO: Use Neil's calculations
        if ($poa->getOriginalPaymentAmount() === 'noRefund') {
            return 0.0;
        }

        $upperRefundAmount = $poa->getOriginalPaymentAmount() === 'orMore';

        if ($poa->getReceivedDate() >= new DateTime('2013-04-01') && $poa->getReceivedDate() < new DateTime('2013-10-01')) {
            return $upperRefundAmount ? 54.0 : 27.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2013-10-01') && $poa->getReceivedDate() < new DateTime('2014-04-01')) {
            return $upperRefundAmount ? 34.0 : 17.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2014-04-01') && $poa->getReceivedDate() < new DateTime('2015-04-01')) {
            return $upperRefundAmount ? 37.0 : 18.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2015-04-01') && $poa->getReceivedDate() < new DateTime('2016-04-01')) {
            return $upperRefundAmount ? 38.0 : 19.0;
        } elseif ($poa->getReceivedDate() >= new DateTime('2016-04-01') && $poa->getReceivedDate() < new DateTime('2017-04-01')) {
            return $upperRefundAmount ? 45.0 : 22.0;
        }

        return 0.0;
    }

    /**
     * @param PoaModel $poa
     * @param float $refundAmount
     * @return float
     */
    public function getAmountWithInterest(PoaModel $poa, $refundAmount): float
    {
        //TODO: Use Neil's calculations
        $now = $this->timeDateService->getTimeNow();
        $diff = $now - $poa->getReceivedDate()->getTimestamp();
        $diffInYears = $diff / 31536000;

        $interestRate = 0.5;

        $refundAmountWithInterest = round($refundAmount * pow(1 + ($interestRate / 100), $diffInYears), 2);

        return $refundAmountWithInterest;
    }

    /**
     * @param ClaimModel $claim
     * @return float
     */
    public function getRefundTotalAmount(ClaimModel $claim): float
    {
        $refundTotalAmount = 0.0;
        foreach ($claim->getPoas() as $poa) {
            $refundAmount = $this->getRefundAmount($poa);
            $refundTotalAmount += $this->getAmountWithInterest($poa, $refundAmount);
        }
        return $refundTotalAmount;
    }
}