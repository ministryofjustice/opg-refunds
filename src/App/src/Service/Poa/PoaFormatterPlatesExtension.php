<?php

namespace App\Service\Poa;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PoaFormatterPlatesExtension implements ExtensionInterface
{
    /**
     * @var PoaFormatter
     */
    private $formatter;

    public function __construct(PoaFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    public function register(Engine $engine)
    {
        $engine->registerFunction('hasSiriusPoas', [$this->formatter, 'hasSiriusPoas']);
        $engine->registerFunction('hasMerisPoas', [$this->formatter, 'hasMerisPoas']);
        $engine->registerFunction('getSiriusPoas', [$this->formatter, 'getSiriusPoas']);
        $engine->registerFunction('getMerisPoas', [$this->formatter, 'getMerisPoas']);
        $engine->registerFunction('getFormattedCaseNumber', [$this->formatter, 'getFormattedCaseNumber']);
        $engine->registerFunction('getOriginalPaymentAmountString', [$this->formatter, 'getOriginalPaymentAmountString']);
        $engine->registerFunction('getFormattedVerificationMatches', [$this->formatter, 'getFormattedVerificationMatches']);
        $engine->registerFunction('isAttorneyVerified', [$this->formatter, 'isAttorneyVerified']);
        $engine->registerFunction('isCaseNumberVerified', [$this->formatter, 'isCaseNumberVerified']);
        $engine->registerFunction('isDonorPostcodeVerified', [$this->formatter, 'isDonorPostcodeVerified']);
        $engine->registerFunction('isAttorneyPostcodeVerified', [$this->formatter, 'isAttorneyPostcodeVerified']);
        $engine->registerFunction('isClaimVerified', [$this->formatter, 'isClaimVerified']);
    }
}