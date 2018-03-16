<?php

namespace App\Service;

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
     * @param $originalPaymentAmount
     * @param $receivedDate
     * @return float
     */
    public static function getRefundAmount($originalPaymentAmount, DateTime $receivedDate): float
    {
        if (empty($originalPaymentAmount) || $originalPaymentAmount === PoaModel::ORIGINAL_PAYMENT_AMOUNT_NO_REFUND) {
            return 0.0;
        }

        $upperRefundAmount = $originalPaymentAmount === PoaModel::ORIGINAL_PAYMENT_AMOUNT_OR_MORE;

        if ($receivedDate >= new DateTime('2013-04-01') && $receivedDate < new DateTime('2013-10-01')) {
            return $upperRefundAmount ? 54.0 : 27.0;
        } elseif ($receivedDate >= new DateTime('2013-10-01') && $receivedDate < new DateTime('2014-04-01')) {
            return $upperRefundAmount ? 34.0 : 17.0;
        } elseif ($receivedDate >= new DateTime('2014-04-01') && $receivedDate < new DateTime('2015-04-01')) {
            return $upperRefundAmount ? 37.0 : 18.0;
        } elseif ($receivedDate >= new DateTime('2015-04-01') && $receivedDate < new DateTime('2016-04-01')) {
            return $upperRefundAmount ? 38.0 : 19.0;
        } elseif ($receivedDate >= new DateTime('2016-04-01') && $receivedDate < new DateTime('2017-04-01')) {
            return $upperRefundAmount ? 45.0 : 22.0;
        }

        return 0.0;
    }

    /**
     * @param string $originalPaymentAmount
     * @param DateTime $receivedDate
     * @param int $refundTime
     * @return float
     */
    public static function getRefundInterestAmount(
        $originalPaymentAmount,
        DateTime $receivedDate,
        int $refundTime
    ): float {
        $refundAmount = self::getRefundAmount($originalPaymentAmount, $receivedDate);

        $diff = $refundTime - $receivedDate->getTimestamp();
        $diffInYears = $diff / 31536000;

        $interestRate = 0.5;

        $refundAmountWithInterest = round($refundAmount * pow(1 + ($interestRate / 100), $diffInYears), 2);

        return $refundAmountWithInterest - $refundAmount;
    }

    /**
     * @param ClaimModel $claim
     * @param int $refundTime
     * @return float
     */
    public static function getRefundTotalAmount(ClaimModel $claim, int $refundTime): float
    {
        if ($claim->getPoas() === null) {
            return 0.0;
        }

        $refundTotalAmount = 0.0;

        foreach ($claim->getPoas() as $poa) {
            $refundAmount = self::getRefundAmount($poa->getOriginalPaymentAmount(), $poa->getReceivedDate());

            $refundInterestAmount = self::getRefundInterestAmount(
                $poa->getOriginalPaymentAmount(),
                $poa->getReceivedDate(),
                $refundTime
            );

            $refundTotalAmount += $refundAmount + $refundInterestAmount;
        }

        return $refundTotalAmount;
    }
}
