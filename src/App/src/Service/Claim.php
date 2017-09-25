<?php

namespace App\Service;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Log as LogModel;

class Claim implements ApiClientInterface
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
     * @return ClaimModel
     * @throws Exception
     */
    public function getClaim(int $claimId, int $userId)
    {
        $claimArray = $this->getApiClient()->httpGet("/v1/cases/claim/$claimId");
        $claim = new ClaimModel($claimArray);

        if ($claim->getAssignedToId() !== $userId) {
            //User is not assigned to chosen claim
            throw new Exception('Access forbidden', 403);
        }

        return $claim;
    }

    /**
     * @param int $claimId
     * @param string $title the new log's title
     * @param string $message the new log's message
     * @return LogModel the newly created log
     */
    public function addLog(int $claimId, string $title, string $message)
    {
        $logArray = $this->getApiClient()->httpPost("/v1/cases/claim/$claimId/log", [
            'title'   => $title,
            'message' => $message,
        ]);

        if (empty($logArray)) {
            return null;
        }

        return new LogModel($logArray);
    }

    /**
     * @param int $claimId
     * @param bool $noSiriusPoas
     * @return null|ClaimModel
     */
    public function setNoSiriusPoas(int $claimId, bool $noSiriusPoas)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'noSiriusPoas' => $noSiriusPoas
        ]);

        if (empty($claimArray)) {
            return null;
        }

        return new ClaimModel($claimArray);
    }

    /**
     * @param int $claimId
     * @param bool $noMerisPoas
     * @return null|ClaimModel
     */
    public function setNoMerisPoas(int $claimId, bool $noMerisPoas)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'noMerisPoas' => $noMerisPoas
        ]);

        if (empty($claimArray)) {
            return null;
        }

        return new ClaimModel($claimArray);
    }
}