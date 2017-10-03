<?php

namespace App\View\Poa;

use App\Service\Poa\PoaFormatter as PoaFormatterService;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PoaFormatterPlatesExtension implements ExtensionInterface
{
    /**
     * @var PoaFormatterService
     */
    private $formatterService;

    public function __construct(PoaFormatterService $formatterService)
    {
        $this->formatterService = $formatterService;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('hasSiriusPoas', [$this->formatterService, 'hasSiriusPoas']);
        $engine->registerFunction('hasMerisPoas', [$this->formatterService, 'hasMerisPoas']);
        $engine->registerFunction('getSiriusPoas', [$this->formatterService, 'getSiriusPoas']);
        $engine->registerFunction('getMerisPoas', [$this->formatterService, 'getMerisPoas']);
        $engine->registerFunction('getFormattedCaseNumber', [$this->formatterService, 'getFormattedCaseNumber']);
        $engine->registerFunction('getOriginalPaymentAmountString', [$this->formatterService, 'getOriginalPaymentAmountString']);
        $engine->registerFunction('getRefundAmountString', [$this->formatterService, 'getRefundAmountString']);
        $engine->registerFunction('getInterestAmountString', [$this->formatterService, 'getInterestAmountString']);
        $engine->registerFunction('getRefundTotalAmountString', [$this->formatterService, 'getRefundTotalAmountString']);
        $engine->registerFunction('getFormattedVerificationMatches', [$this->formatterService, 'getFormattedVerificationMatches']);
        $engine->registerFunction('isAttorneyVerified', [$this->formatterService, 'isAttorneyVerified']);
        $engine->registerFunction('isCaseNumberVerified', [$this->formatterService, 'isCaseNumberVerified']);
        $engine->registerFunction('isDonorPostcodeVerified', [$this->formatterService, 'isDonorPostcodeVerified']);
        $engine->registerFunction('isAttorneyPostcodeVerified', [$this->formatterService, 'isAttorneyPostcodeVerified']);
        $engine->registerFunction('isClaimVerified', [$this->formatterService, 'isClaimVerified']);
        $engine->registerFunction('isClaimComplete', [$this->formatterService, 'isClaimComplete']);
    }
}