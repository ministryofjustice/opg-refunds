<?php

namespace App\View\Poa;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PoaFormatterPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('getFormattedCaseNumber', [$this, 'getFormattedCaseNumber']);
        $engine->registerFunction('getOriginalPaymentAmountString', [$this, 'getOriginalPaymentAmountString']);
        $engine->registerFunction('getFormattedVerificationMatches', [$this, 'getFormattedVerificationMatches']);
    }

    public function getFormattedCaseNumber(PoaModel $poa)
    {
        switch ($poa->getSystem()) {
            case PoaModel::SYSTEM_SIRIUS:
                return $poa->getCaseNumber();
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
                return 'No fee paid';
            default:
                return '';
        }
    }

    public function getFormattedVerificationMatches(PoaModel $poa)
    {
        if ($poa->getVerifications() === null) {
            return '';
        }

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
            case VerificationModel::TYPE_ATTORNEY:
                return 'Attorney name, Attorney date of birth';
            case VerificationModel::TYPE_ATTORNEY_NAME:
                return 'Attorney name';
            case VerificationModel::TYPE_ATTORNEY_DOB:
                return 'Attorney date of birth';
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
}
