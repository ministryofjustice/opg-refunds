<?php

namespace Opg\Refunds\Caseworker\DataModel;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim;

/**
 * Class RejectionReasonsFormatter
 * @package Opg\Refunds\Caseworker\DataModel
 */
class RejectionReasonsFormatter
{
    public static function getRejectionReasonText(string $rejectionReason)
    {
        switch ($rejectionReason) {
            case Claim::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND:
                return 'No eligible POAs found';
            case Claim::REJECTION_REASON_PREVIOUSLY_REFUNDED:
                return 'POA already refunded';
            case Claim::REJECTION_REASON_NO_FEES_PAID:
                return 'No fees paid';
            case Claim::REJECTION_REASON_CLAIM_NOT_VERIFIED:
                return 'Details not verified';
            default:
                return 'Unknown';
        }
    }
}