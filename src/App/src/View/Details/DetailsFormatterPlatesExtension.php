<?php

namespace App\View\Details;

use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name as NameModel;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use InvalidArgumentException;

/**
 * Class DetailsFormatterPlatesExtension
 * @package App\View\Details
 */
class DetailsFormatterPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('getApplicantName', [$this, 'getApplicantName']);
        $engine->registerFunction('getPaymentDetailsUsedText', [$this, 'getPaymentDetailsUsedText']);
        $engine->registerFunction('shouldShowPaymentDetailsUsedCountWarning', [$this, 'shouldShowPaymentDetailsUsedCountWarning']);
        $engine->registerFunction('getRejectionReasonsText', [$this, 'getRejectionReasonsText']);
        $engine->registerFunction('getStatusText', [$this, 'getStatusText']);
        $engine->registerFunction('getPercentage', [$this, 'getPercentage']);
        $engine->registerFunction('getValueWithPercentage', [$this, 'getValueWithPercentage']);
    }

    public function getApplicantName(ApplicationModel $application)
    {
        if ($application->getApplicant() === 'donor') {
            return "{$application->getDonor()->getCurrent()->getName()->getFormattedName()} (Donor)";
        } elseif ($application->getApplicant() === 'attorney') {
            return "{$application->getAttorney()->getCurrent()->getName()->getFormattedName()} (Attorney)";
        }

        return '';
    }

    public function getPaymentDetailsUsedText($accountHashCount)
    {
        if ($accountHashCount === null) {
            throw new InvalidArgumentException('Account hash count must be set');
        }

        if ($accountHashCount < 1) {
            throw new InvalidArgumentException('Account hash count is set to an invalid value: ' . $accountHashCount);
        }

        if ($accountHashCount === 1) {
            return "Used once";
        }

        if ($accountHashCount === 2) {
            return "Used twice";
        }

        if ($accountHashCount > 2) {
            return "Used {$accountHashCount} times";
        }
    }

    public function shouldShowPaymentDetailsUsedCountWarning($accountHashCount)
    {
        return $accountHashCount > 1;
    }

    public function getRejectionReasonsText(string $rejectionReason)
    {
        switch ($rejectionReason) {
            case ClaimModel::REJECTION_REASON_NO_ELIGIBLE_POAS_FOUND:
                return 'No eligible POAs found';
            case ClaimModel::REJECTION_REASON_PREVIOUSLY_REFUNDED:
                return 'POA already refunded';
            case ClaimModel::REJECTION_REASON_NO_FEES_PAID:
                return 'No fees paid';
            case ClaimModel::REJECTION_REASON_CLAIM_NOT_VERIFIED:
                return 'Details not verified';
            case ClaimModel::REJECTION_REASON_OTHER:
                return 'Other';
            default:
                return 'Unknown';
        }
    }

    public function getStatusText(string $status)
    {
        switch ($status) {
            case ClaimModel::STATUS_PENDING:
                return 'Pending';
            case ClaimModel::STATUS_IN_PROGRESS:
                return 'In Progress';
            case ClaimModel::STATUS_REJECTED:
                return 'Rejected';
            case ClaimModel::STATUS_ACCEPTED:
                return 'Accepted';
            default:
                return 'Unknown';
        }
    }

    public function getPercentage(int $total, int $value)
    {
        if ($total === 0) {
            return '0.00%';
        }

        $percentage = ($value / $total) * 100;

        return sprintf("%.2f%%", $percentage);
    }

    public function getValueWithPercentage(int $total, int $value)
    {
        return "{$value} ({$this->getPercentage($total, $value)})";
    }
}
