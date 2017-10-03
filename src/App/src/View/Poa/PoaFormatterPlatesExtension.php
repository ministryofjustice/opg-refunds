<?php

namespace App\View\Poa;

use App\Service\Poa\Poa as PoaService;
use App\Service\Refund\Refund as RefundService;
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

    /**
     * @var RefundService
     */
    private $refundService;

    public function __construct(PoaService $poaService, RefundService $refundService)
    {
        $this->poaService = $poaService;
        $this->refundService = $refundService;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('hasSiriusPoas', [$this->poaService, 'hasSiriusPoas']);
        $engine->registerFunction('hasMerisPoas', [$this->poaService, 'hasMerisPoas']);
        $engine->registerFunction('getSiriusPoas', [$this->poaService, 'getSiriusPoas']);
        $engine->registerFunction('getMerisPoas', [$this->poaService, 'getMerisPoas']);
        $engine->registerFunction('getFormattedCaseNumber', [$this, 'getFormattedCaseNumber']);
        $engine->registerFunction('getOriginalPaymentAmountString', [$this, 'getOriginalPaymentAmountString']);
        $engine->registerFunction('getRefundAmountString', [$this, 'getRefundAmountString']);
        $engine->registerFunction('getInterestAmountString', [$this, 'getInterestAmountString']);
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