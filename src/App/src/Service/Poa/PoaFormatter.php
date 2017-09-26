<?php

namespace App\Service\Poa;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;

/**
 * Class PoaFormatter
 * @package App\Service\Poa
 */
class PoaFormatter
{
    public function hasSiriusPoas(ClaimModel $claim)
    {
        return $this->hasSystemPoas($claim, 'sirius');
    }

    public function hasMerisPoas(ClaimModel $claim)
    {
        return $this->hasSystemPoas($claim, 'meris');
    }

    public function getSiriusPoas(ClaimModel $claim)
    {
        return $this->getSystemPoas($claim, 'sirius');
    }

    public function getMerisPoas(ClaimModel $claim)
    {
        return $this->getSystemPoas($claim, 'meris');
    }

    public function getFormattedCaseNumber(PoaModel $poa)
    {
        switch ($poa->getSystem()) {
            case 'sirius':
                return join('-', str_split($poa->getCaseNumber(), 4));
            case 'meris':
                return $poa->getCaseNumber();
            default:
                return $poa->getCaseNumber();
        }
    }

    public function getOriginalPaymentAmountString(PoaModel $poa)
    {
        switch ($poa->getOriginalPaymentAmount()) {
            case 'orMore':
                return '£110 or more';
            case 'lessThan':
                return 'Less than £110';
            case 'noRefund':
                return 'No refund amount';
            default:
                return '';
        }
    }

    public function getFormattedVerificationMatches(PoaModel $poa)
    {
        $verificationStrings = [];

        foreach ($poa->getVerifications() as $verification) {
            if ($verification->isPasses()) {
                $verificationStrings[] = $this->getFormattedVerificationMatch($verification);
            }
        }

        if (count($verificationStrings) === 0) {
            return 'None';
        }

        return join(', ', $verificationStrings);
    }

    private function getFormattedVerificationMatch(VerificationModel $verification)
    {
        switch ($verification->getType()) {
            case 'attorney':
                return 'Attorney details';
            case 'case-number':
                return 'Case number';
            case 'donor-postcode':
                return 'Donor postcode' ;
            case 'attorney-postcode':
                return 'Attorney postcode';
            default:
                return '';
        }
    }

    private function hasSystemPoas(ClaimModel $claim, string $system)
    {
        if ($claim->getPoas() === null) {
            return false;
        }

        foreach ($claim->getPoas() as $poa) {
            if ($poa->getSystem() === $system) {
                return true;
            }
        }

        return false;
    }

    private function getSystemPoas(ClaimModel $claim, string $system)
    {
        $poas = [];

        if ($claim->getPoas() === null) {
            return $poas;
        }

        foreach ($claim->getPoas() as $poa) {
            if ($poa->getSystem() === $system) {
                $poas[] = $poa;
            }
        }

        return $poas;
    }
}