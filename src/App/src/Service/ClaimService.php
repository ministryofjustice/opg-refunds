<?php

namespace App\Service;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;

class ClaimService implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * Retrieves the next available, unassigned Claim, assigns it to the currently logged in user and returns it
     *
     * @param int $userId user id to assign claim to
     * @return int the id of the next case to process. Will be zero if none was assigned
     */
    public function assignNextClaim(int $userId)
    {
        //  GET on caseworker's case endpoint without an id means get next refund case
        $result = $this->getApiClient()->httpPut("/v1/cases/user/$userId/claim", []);

        return $result['assignedClaimId'];
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @return Claim
     * @throws Exception
     */
    public function getClaim(int $claimId, int $userId)
    {
        $claimArray = $this->getApiClient()->httpGet("/v1/cases/claim/$claimId");
        $claim = new Claim($claimArray);

        if ($claim->getAssignedToId() !== $userId) {
            //User is not assigned to chosen claim
            throw new Exception('Access forbidden', 403);
        }

        return $claim;
    }
}