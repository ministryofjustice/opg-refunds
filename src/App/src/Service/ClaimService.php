<?php

namespace App\Service;

use Opg\Refunds\Caseworker\DataModel\Cases\Claim;

class ClaimService extends AbstractApiClientService
{
    /**
     * Retrieves the next available, unassigned Claim, assigns it to the currently logged in user and returns it
     *
     * @return Claim|null the next case to process
     */
    public function getNextClaim()
    {
        //  GET on caseworker's case endpoint without an id means get next refund case
        $claimArray = $this->getApiClient()->httpGet('/v1/cases/caseworker/claim');

        if ($claimArray === null) {
            return null;
        }

        $claim = new Claim($claimArray);
        return $claim;
    }
}