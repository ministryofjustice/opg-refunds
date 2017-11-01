<?php

namespace App\Service\Claim;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\ClaimSummaryPage;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Verification as VerificationModel;

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
        $result = $this->getApiClient()->httpPut("/v1/cases/user/$userId/claim", []);

        return $result['assignedClaimId'];
    }

    /**
     * Assigns a specific claim to a specific user
     *
     * @param int $claimId
     * @param int $userId
     * @return int the id of the next case to process. Will be zero if none was assigned
     */
    public function assignClaim(int $claimId, int $userId)
    {
        $result = $this->getApiClient()->httpPut("/v1/cases/user/$userId/claim/$claimId", []);

        return $result['assignedClaimId'];
    }

    /**
     * Removes the assigned user from the claim making it available for another caseworker
     *
     * @param int $claimId
     * @param int $userId
     */
    public function unAssignClaim(int $claimId, int $userId)
    {
        $this->getApiClient()->httpDelete("/v1/cases/user/$userId/claim/$claimId");
    }

    /**
     * @param int $claimId
     * @return ClaimModel
     * @throws Exception
     */
    public function getClaim(int $claimId)
    {
        $claimData = $this->getApiClient()->httpGet("/v1/cases/claim/$claimId");

        $claim = $this->createDataModel($claimData);

        if (!$claim instanceof ClaimModel) {
            throw new Exception('Claim not found', 404);
        }

        return $claim;
    }

    /**
     * Search claims
     *
     * @param int|null $page
     * @param int|null $pageSize
     * @param string|null $search
     * @param int|null $assignedToId
     * @param string|null $status
     * @param string|null $accountHash
     * @param string|null $orderBy
     * @param string|null $sort
     * @return ClaimSummaryPage
     */
    public function searchClaims(int $page = null, int $pageSize = null, string $search = null, int $assignedToId = null, string $status = null, string $accountHash = null, string $orderBy = null, string $sort = null)
    {
        $queryParameters = [];
        if ($page != null) {
            $queryParameters['page'] = $page;
        }
        if ($pageSize != null) {
            $queryParameters['pageSize'] = $pageSize;
        }
        if ($search != null) {
            $queryParameters['search'] = $search;
        }
        if ($assignedToId != null) {
            $queryParameters['assignedToId'] = $assignedToId;
        }
        if ($status != null) {
            $queryParameters['status'] = $status;
        }
        if ($accountHash != null) {
            $queryParameters['accountHash'] = $accountHash;
        }
        if ($orderBy != null) {
            $queryParameters['orderBy'] = $orderBy;
        }
        if ($sort != null) {
            $queryParameters['sort'] = $sort;
        }

        $url = '/v1/cases/claim/search';
        if ($queryParameters) {
            $url .= '?' . http_build_query($queryParameters);
        }

        $claimPageData = $this->getApiClient()->httpGet($url);
        $claimSummaryPage = new ClaimSummaryPage($claimPageData);

        return $claimSummaryPage;
    }

    /**
     * @param int $claimId
     * @param string $title the new note's title
     * @param string $message the new note's message
     * @return NoteModel the newly created note
     */
    public function addNote(int $claimId, string $title, string $message)
    {
        $noteArray = $this->getApiClient()->httpPost("/v1/cases/claim/$claimId/note", [
            'title'   => $title,
            'message' => $message,
        ]);

        if (empty($noteArray)) {
            return null;
        }

        return new NoteModel($noteArray);
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

        return $this->createDataModel($claimArray);
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

        return $this->createDataModel($claimArray);
    }

    /**
     * @param ClaimModel $claim
     * @param PoaModel $poa
     * @return null|ClaimModel
     */
    public function addPoa(ClaimModel $claim, PoaModel $poa)
    {
        $this->updatePoaCaseNumberVerification($claim, $poa);

        $claimArray = $this->getApiClient()->httpPost("/v1/cases/claim/{$claim->getId()}/poa", $poa->getArrayCopy());

        return $this->createDataModel($claimArray);
    }

    public function editPoa(ClaimModel $claim, PoaModel $poa, int $poaId)
    {
        $this->updatePoaCaseNumberVerification($claim, $poa);

        $claimArray = $this->getApiClient()->httpPut("/v1/cases/claim/{$claim->getId()}/poa/{$poaId}", $poa->getArrayCopy());

        return $this->createDataModel($claimArray);
    }

    public function deletePoa($claimId, $poaId)
    {
        $claimArray = $this->getApiClient()->httpDelete("/v1/cases/claim/{$claimId}/poa/{$poaId}");

        return $this->createDataModel($claimArray);
    }

    public function setRejectionReason(int $claimId, $rejectionReason, $rejectionReasonDescription)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'status'                     => ClaimModel::STATUS_REJECTED,
            'rejectionReason'            => $rejectionReason,
            'rejectionReasonDescription' => $rejectionReasonDescription
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setStatusAccepted(int $claimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/cases/claim/$claimId", [
            'status' => ClaimModel::STATUS_ACCEPTED
        ]);

        return $this->createDataModel($claimArray);
    }

    /**
     * @param ClaimModel $claim
     * @param PoaModel $poa
     */
    private function updatePoaCaseNumberVerification(ClaimModel $claim, PoaModel $poa)
    {
        $poaCaseNumber = $claim->getApplication()->getCaseNumber()->getPoaCaseNumber();

        if ($poaCaseNumber !== null) {
            $caseNumber = $poa->getCaseNumber();

            //Strip out meris sequence number if present
            $poaCaseNumber = strpos($poaCaseNumber, '/') ? substr($poaCaseNumber, 0, strpos($poaCaseNumber, '/')) : $poaCaseNumber;
            $caseNumber = strpos($caseNumber, '/') ? substr($caseNumber, 0, strpos($caseNumber, '/')) : $caseNumber;

            if ($poaCaseNumber === $caseNumber) {
                //Add verification for case number
                $verifications = $poa->getVerifications();
                $verifications[] = new VerificationModel([
                    'type' => VerificationModel::TYPE_CASE_NUMBER,
                    'passes' => 'yes'
                ]);
                $poa->setVerifications($verifications);
            }
        }
    }

    /**
     * Create model from array data
     *
     * @param array|null $data
     * @return null|ClaimModel
     */
    private function createDataModel(array $data = null)
    {
        if (is_array($data) && !empty($data)) {
            return new ClaimModel($data);
        }

        return null;
    }

    /**
     * Create a collection (array) of models
     *
     * @param array|null $data
     * @return array
     */
    private function createModelCollection(array $data = null)
    {
        $models = [];

        if (is_array($data)) {
            foreach ($data as $dataItem) {
                $models[] = $this->createDataModel($dataItem);
            }
        };

        return $models;
    }
}
