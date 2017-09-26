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

    public function isAttorneyVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, 'attorney');
    }

    public function isCaseNumberVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, 'case-number');
    }

    public function isDonorPostcodeVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, 'donor-postcode');
    }

    public function isAttorneyPostcodeVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, 'attorney-postcode');
    }

    public function isClaimVerified(ClaimModel $claim)
    {
        $verificationCount = 0;

        if ($this->isAttorneyVerified($claim)) {
            //Means that both the attorney's name and dob have been verified so counts for 2
            $verificationCount+=2;
        }

        if ($this->isCaseNumberVerified($claim)) {
            $verificationCount++;
        }

        if ($this->isDonorPostcodeVerified($claim)) {
            $verificationCount++;
        }

        if ($this->isAttorneyPostcodeVerified($claim)) {
            $verificationCount++;
        }

        return $verificationCount >= 3;
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

        return join('<br/>', $verificationStrings);
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

    private function isVerified(ClaimModel $claim, string $verificationType): bool
    {
        if ($claim->getPoas() === null) {
            return false;
        }

        foreach ($claim->getPoas() as $poa) {
            foreach ($poa->getVerifications() as $verification) {
                if ($verification->getType() === $verificationType && $verification->isPasses()) {
                    return true;
                }
            }
        }

        return false;
    }
}