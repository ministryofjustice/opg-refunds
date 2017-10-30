<?php

namespace App\View\Poa;

use App\Service\Poa\Poa as PoaService;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PoaFormatterPlatesExtension implements ExtensionInterface
{
    /**
     * @var PoaService
     */
    private $poaService;

    public function __construct(PoaService $poaService)
    {
        $this->poaService = $poaService;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('hasSiriusPoas', [$this->poaService, 'hasSiriusPoas']);
        $engine->registerFunction('hasMerisPoas', [$this->poaService, 'hasMerisPoas']);
        $engine->registerFunction('getSiriusPoas', [$this->poaService, 'getSiriusPoas']);
        $engine->registerFunction('getMerisPoas', [$this->poaService, 'getMerisPoas']);
        $engine->registerFunction('getFormattedCaseNumber', [$this, 'getFormattedCaseNumber']);
        $engine->registerFunction('getOriginalPaymentAmountString', [$this, 'getOriginalPaymentAmountString']);
        $engine->registerFunction('getMoneyString', [$this, 'getMoneyString']);
        $engine->registerFunction('getRefundTotalAmountString', [$this, 'getRefundTotalAmountString']);
        $engine->registerFunction('getFormattedVerificationMatches', [$this, 'getFormattedVerificationMatches']);
        $engine->registerFunction('isAttorneyVerified', [$this->poaService, 'isAttorneyVerified']);
        $engine->registerFunction('isCaseNumberVerified', [$this->poaService, 'isCaseNumberVerified']);
        $engine->registerFunction('isDonorPostcodeVerified', [$this->poaService, 'isDonorPostcodeVerified']);
        $engine->registerFunction('isAttorneyPostcodeVerified', [$this->poaService, 'isAttorneyPostcodeVerified']);
        $engine->registerFunction('isClaimVerified', [$this->poaService, 'isClaimVerified']);
        $engine->registerFunction('isClaimComplete', [$this->poaService, 'isClaimComplete']);
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
                return 'No fee paid';
            default:
                return '';
        }
    }

    public function getMoneyString(float $amount)
    {
        return money_format('£%i', $amount);
    }

    public function getRefundTotalAmountString(ClaimModel $claim)
    {
        if ($claim->getPoas() === null) {
            return '£0.00';
        }

        $refundTotalAmount = 0.0;
        foreach ($claim->getPoas() as $poa) {
            $refundTotalAmount += $poa->getRefundAmount() + $poa->getRefundInterestAmount();
        }

        return $this->getMoneyString($refundTotalAmount);
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
}