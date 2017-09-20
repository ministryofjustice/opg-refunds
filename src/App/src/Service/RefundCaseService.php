<?php

namespace App\Service;

use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase;

class RefundCaseService extends AbstractApiClientService
{
    /**
     * Retrieves the next available, unassigned RefundCase, assigns it to the currently logged in user and returns it
     *
     * @return RefundCase|null the next case to process
     */
    public function getNextRefundCase()
    {
        //  GET on caseworker's case endpoint without an id means get next refund case
        $refundCaseArray = $this->getApiClient()->httpGet('/v1/cases/caseworker/case');

        if ($refundCaseArray === null) {
            return null;
        }

        $refundCase = new RefundCase($refundCaseArray);
        return $refundCase;
    }
}