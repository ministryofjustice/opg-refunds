<?php

namespace App\Service\Details;

use InvalidArgumentException;
use Opg\Refunds\Caseworker\DataModel\Applications\Application as ApplicationModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Common\Name as NameModel;

/**
 * Class DetailsFormatter
 * @package App\Service\Details
 */
class DetailsFormatter
{
    public function getFormattedName(NameModel $name)
    {
        return "{$name->getTitle()} {$name->getFirst()} {$name->getLast()}";
    }

    public function getApplicantName(ApplicationModel $application)
    {
        if ($application->getApplicant() === 'donor') {
            return "{$this->getFormattedName($application->getDonor()->getName())} (Donor)";
        } elseif ($application->getApplicant() === 'attorney') {
            return "{$this->getFormattedName($application->getAttorney()->getName())} (Attorney)";
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

        if ($accountHashCount === 2) {
            return "Payment details used {$accountHashCount} times";
        }
    }

    public function shouldShowPaymentDetailsUsedCountWarning(int $accountHashCount)
    {
        return $accountHashCount > 2;
    }
}