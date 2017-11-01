<?php

namespace App\Service\Poa;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;

/**
 * Class PoaService
 * @package App\Service\Poa
 */
class Poa
{
    public function hasSiriusPoas(ClaimModel $claim)
    {
        return $this->hasSystemPoas($claim, PoaModel::SYSTEM_SIRIUS);
    }

    public function hasMerisPoas(ClaimModel $claim)
    {
        return $this->hasSystemPoas($claim, PoaModel::SYSTEM_MERIS);
    }

    public function getSiriusPoas(ClaimModel $claim)
    {
        return $this->getSystemPoas($claim, PoaModel::SYSTEM_SIRIUS);
    }

    public function getMerisPoas(ClaimModel $claim)
    {
        return $this->getSystemPoas($claim, PoaModel::SYSTEM_MERIS);
    }

    public function isAttorneyVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, VerificationModel::TYPE_ATTORNEY);
    }

    public function isCaseNumberVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, VerificationModel::TYPE_CASE_NUMBER);
    }

    public function isDonorPostcodeVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, VerificationModel::TYPE_DONOR_POSTCODE);
    }

    public function isAttorneyPostcodeVerified(ClaimModel $claim): bool
    {
        return $this->isVerified($claim, VerificationModel::TYPE_ATTORNEY_POSTCODE);
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

    public function isClaimComplete(ClaimModel $claim)
    {
        return $this->allPoasComplete($claim)
            && ($claim->isNoSiriusPoas() || $this->hasSiriusPoas($claim))
            && ($claim->isNoMerisPoas() || $this->hasMerisPoas($claim));
    }

    public function isClaimRefundNonZero(ClaimModel $claim)
    {
        $refundTotalAmount = 0.0;

        foreach ($claim->getPoas() as $poa) {
            $refundTotalAmount += $poa->getRefundAmount() + $poa->getRefundInterestAmount();
        }

        return $refundTotalAmount > 0;
    }

    private function allPoasComplete(ClaimModel $claim): bool
    {
        if ($claim->getPoas() === null) {
            return true;
        }

        foreach ($claim->getPoas() as $poa) {
            if (!$poa->isComplete()) {
                return false;
            }
        }

        return true;
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

    private function isVerified(ClaimModel $claim, string $verificationType): bool
    {
        if ($claim->getPoas() === null) {
            return false;
        }

        foreach ($claim->getPoas() as $poa) {
            if ($poa->getVerifications() === null) {
                continue;
            }

            foreach ($poa->getVerifications() as $verification) {
                if ($verification->getType() === $verificationType && $verification->isPasses()) {
                    return true;
                }
            }
        }

        return false;
    }
}