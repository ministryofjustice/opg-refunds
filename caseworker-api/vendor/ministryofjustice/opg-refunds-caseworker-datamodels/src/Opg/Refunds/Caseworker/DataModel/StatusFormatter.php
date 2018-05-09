<?php

namespace Opg\Refunds\Caseworker\DataModel;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;

/**
 * Class StatusFormatter
 * @package Opg\Refunds\Caseworker\DataModel
 */
class StatusFormatter
{
    /**
     * @param string $status
     * @return string
     */
    public static function getStatusText(string $status)
    {
        switch ($status) {
            case ClaimModel::STATUS_PENDING:
                return 'Pending';
            case ClaimModel::STATUS_IN_PROGRESS:
                return 'In Progress';
            case ClaimModel::STATUS_DUPLICATE:
                return 'Duplicate';
            case ClaimModel::STATUS_REJECTED:
                return 'Rejected';
            case ClaimModel::STATUS_ACCEPTED:
                return 'Accepted';
            case ClaimModel::STATUS_WITHDRAWN:
                return 'Withdrawn';
            default:
                return 'Unknown';
        }
    }
}