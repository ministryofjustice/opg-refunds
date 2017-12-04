<?php

namespace Opg\Refunds\Caseworker\DataModel;

use Opg\Refunds\Caseworker\DataModel\Applications\AssistedDigital;

/**
 * Class PhoneClaimTypeFormatter
 * @package Opg\Refunds\Caseworker\DataModel
 */
class PhoneClaimTypeFormatter
{
    public static function getPhoneClaimTypeText(string $phoneClaimType)
    {
        switch ($phoneClaimType) {
            case AssistedDigital::TYPE_DONOR_DECEASED:
                return 'Donor deceased';
            case AssistedDigital::TYPE_ASSISTED_DIGITAL:
                return 'Assisted digital';
            case AssistedDigital::TYPE_REFUSE_CLAIM_ONLINE:
                return 'Doesn\'t want to claim online';
            case AssistedDigital::TYPE_DEPUTY:
                return 'Deputy';
            case AssistedDigital::TYPE_CHEQUE:
                return 'I want a cheque';
            default:
                return 'Unknown';
        }
    }
}