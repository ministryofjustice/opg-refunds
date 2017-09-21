<?php

namespace App\Service;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;

class ClaimService implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * Retrieves the next available, unassigned Claim, assigns it to the currently logged in user and returns it
     *
     * @param int $userId user id to assign claim to
     * @return null|Claim the next case to process
     */
    public function assignNextClaim(int $userId)
    {
        //  GET on caseworker's case endpoint without an id means get next refund case
        $claimArray = $this->getApiClient()->httpPut("/v1/cases/user/$userId/claim", []);

        if ($claimArray === null || empty($claimArray)) {
            return null;
        }

        $claim = new Claim($claimArray);
        return $claim;
    }
}