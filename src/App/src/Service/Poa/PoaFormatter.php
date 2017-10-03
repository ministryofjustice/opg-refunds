<?php

namespace App\Service\Poa;

use App\Service\Refund\Refund as RefundService;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;

/**
 * Class PoaFormatter
 * @package App\Service\Poa
 */
class PoaFormatter
{
    /**
     * @var RefundService
     */
    private $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

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

    public function getFormattedCaseNumber(PoaModel $poa)
    {
        switch ($poa->getSystem()) {
            case PoaModel::SYSTEM_SIRIUS:
                return join('-', str_split($poa->getCaseNumber(), 4));
            case PoaModel::SYSTEM_MERIS:
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

    public function getRefundAmountString(PoaModel $poa)
    {
        return money_format('£%i', $this->refundService->getRefundAmount($poa));
    }

    public function getInterestAmountString(PoaModel $poa)
    {
        $refundAmount = $this->refundService->getRefundAmount($poa);
        $refundAmountWithInterest = $this->refundService->getAmountWithInterest($poa, $refundAmount);
        $interest = $refundAmountWithInterest - $refundAmount;
        return money_format('£%i', $interest);
    }

    public function getRefundTotalAmountString(ClaimModel $claim)
    {
        if ($claim->getPoas() === null) {
            return '£0.00';
        }

        return money_format('£%i', $this->refundService->getRefundTotalAmount($claim));
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

    public function isClaimComplete(ClaimModel $claim)
    {
        return ($claim->isNoSiriusPoas() || $this->hasSiriusPoas($claim))
            && ($claim->isNoMerisPoas() || $this->hasMerisPoas($claim));
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
            case VerificationModel::TYPE_ATTORNEY:
                return 'Attorney details';
            case VerificationModel::TYPE_CASE_NUMBER:
                return 'Case number';
            case VerificationModel::TYPE_DONOR_POSTCODE:
                return 'Donor postcode' ;
            case VerificationModel::TYPE_ATTORNEY_POSTCODE:
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
