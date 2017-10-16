<?php

namespace App\Service;

use DateTime;

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
    public static function getRefundAmount(string $originalPaymentAmount, DateTime $receivedDate): float
    {
        //TODO: Use Neil's calculations
        if ($originalPaymentAmount === 'noRefund') {
            return 0.0;
        }

        $upperRefundAmount = $originalPaymentAmount === 'orMore';

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
        string $originalPaymentAmount,
        DateTime $receivedDate,
        int $refundTime
    ): float {
        //TODO: Use Neil's calculations
        $refundAmount = self::getRefundAmount($originalPaymentAmount, $receivedDate);

        $diff = $refundTime - $receivedDate->getTimestamp();
        $diffInYears = $diff / 31536000;

        $interestRate = 0.5;

        $refundAmountWithInterest = round($refundAmount * pow(1 + ($interestRate / 100), $diffInYears), 2);

        return $refundAmountWithInterest - $refundAmount;
    }
}
