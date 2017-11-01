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
        $engine->registerFunction('getFormattedName', [$this, 'getFormattedName']);
        $engine->registerFunction('getApplicantName', [$this, 'getApplicantName']);
        $engine->registerFunction('getPaymentDetailsUsedText', [$this, 'getPaymentDetailsUsedText']);
        $engine->registerFunction('shouldShowPaymentDetailsUsedCountWarning', [$this, 'shouldShowPaymentDetailsUsedCountWarning']);
        $engine->registerFunction('getRejectionReasonsText', [$this, 'getRejectionReasonsText']);
        $engine->registerFunction('getStatusText', [$this, 'getStatusText']);
    }

    public function getFormattedName(NameModel $name)
    {
        return "{$name->getTitle()} {$name->getFirst()} {$name->getLast()}";
    }

    public function getApplicantName(ApplicationModel $application)
    {
        if ($application->getApplicant() === 'donor') {
            return "{$this->getFormattedName($application->getDonor()->getCurrent()->getName())} (Donor)";
        } elseif ($application->getApplicant() === 'attorney') {
            return "{$this->getFormattedName($application->getAttorney()->getCurrent()->getName())} (Attorney)";
        }

        return '';
    }

    public function getPaymentDetailsUsedText(int $accountHashCount)
    {
        if ($accountHashCount === null) {
            throw new InvalidArgumentException('Account hash count must be set');
        }

        if ($accountHashCount < 1) {
            throw new InvalidArgumentException('Account hash count is set to an invalid value: ' . $accountHashCount);
        }

        if ($accountHashCount === 1) {
            return "Payment details used once";
        }

        if ($accountHashCount === 2) {
            return "Payment details used twice";
        }

        if ($accountHashCount > 2) {
            return "Payment details used {$accountHashCount} times";
        }
    }

    public function shouldShowPaymentDetailsUsedCountWarning(int $accountHashCount)
    {
        return $accountHashCount > 2;
    }

    public function getRejectionReasonsText(string $rejectionReason)
    {
        switch ($rejectionReason) {
            case 'notInDateRange':
                return 'Not in date range';
            case 'noDonorLpaFound':
                return 'LPA for associated donor could not be found';
            case 'previouslyRefunded':
                return 'Refund already given';
            case 'noFeesPaid':
                return 'No fees paid';
            case 'claimNotVerified':
                return 'Claim isn’t verified';
            case 'other':
                return 'Other';
            default:
                return 'Unknown';
        }
    }

    public static function getStatusText(string $status)
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
}
