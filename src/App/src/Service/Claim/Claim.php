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
        $result = $this->getApiClient()->httpPut("/v1/user/$userId/claim");

        return $result['assignedClaimId'];
    }

    /**
     * Assigns a specific claim to a specific user
     *
     * @param int $claimId
     * @param int $userId
     * @param string $reason
     * @return array containing assignedClaimId (the id of the next case to process, will be zero if none was assigned) and assignedToName
     */
    public function assignClaim(int $claimId, int $userId, string $reason)
    {
        $result = $this->getApiClient()->httpPut("/v1/user/$userId/claim/$claimId", ['reason' => $reason]);

        return $result;
    }

    /**
     * Removes the assigned user from the claim making it available for another caseworker
     *
     * @param int $claimId
     * @param int $userId
     */
    public function unAssignClaim(int $claimId, int $userId)
    {
        $this->getApiClient()->httpDelete("/v1/user/$userId/claim/$claimId");
    }

    /**
     * @param int $claimId
     * @return ClaimModel
     * @throws Exception
     */
    public function getClaim(int $claimId)
    {
        $claimData = $this->getApiClient()->httpGet("/v1/claim/$claimId");

        $claim = $this->createDataModel($claimData);

        if (!$claim instanceof ClaimModel) {
            throw new Exception('Claim not found', 404);
        }

        return $claim;
    }

    /**
     * Search claims
     *
     * @param array $searchParameters
     * @return ClaimSummaryPage
     */
    public function searchClaims(array $searchParameters)
    {
        $queryParameters = $this->getSearchQueryParameters($searchParameters);

        $claimPageData = $this->getApiClient()->httpGet('/v1/claim/search', $queryParameters);
        $claimSummaryPage = new ClaimSummaryPage($claimPageData);

        return $claimSummaryPage;
    }

    /**
     * Download all the claim summaries specified by the search parameters in a spreadsheet
     *
     * @param array $searchParameters
     * @return array containing spreadsheet stream
     */
    public function getSearchClaimsSpreadsheet(array $searchParameters)
    {
        $queryParameters = $this->getSearchQueryParameters($searchParameters);

        $response = $this->getApiClient()->httpGetResponse('/v1/claim/search/download', $queryParameters);

        $fileContents = $response->getBody();
        $contentDisposition = $response->getHeaderLine('Content-Disposition');
        $fileName = substr($contentDisposition, strpos($contentDisposition, '=') + 1);
        $contentLength = $response->getHeaderLine('Content-Length');

        return [
            'stream' => $fileContents,
            'name'   => $fileName,
            'length' => $contentLength
        ];
    }

    /**
     * @param int $claimId
     * @param string $type the new note's type
     * @param string $message the new note's message
     * @return NoteModel the newly created note
     */
    public function addNote(int $claimId, string $type, string $message)
    {
        $noteArray = $this->getApiClient()->httpPost("/v1/claim/$claimId/note", [
            'type'   => $type,
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
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
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
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
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

        $claimArray = $this->getApiClient()->httpPost("/v1/claim/{$claim->getId()}/poa", $poa->getArrayCopy());

        return $this->createDataModel($claimArray);
    }

    public function editPoa(ClaimModel $claim, PoaModel $poa, int $poaId)
    {
        $this->updatePoaCaseNumberVerification($claim, $poa);

        $claimArray = $this->getApiClient()->httpPut("/v1/claim/{$claim->getId()}/poa/{$poaId}", $poa->getArrayCopy());

        return $this->createDataModel($claimArray);
    }

    public function deletePoa($claimId, $poaId)
    {
        $claimArray = $this->getApiClient()->httpDelete("/v1/claim/{$claimId}/poa/{$poaId}");

        return $this->createDataModel($claimArray);
    }

    public function setRejectionReason(int $claimId, $rejectionReason, $rejectionReasonDescription)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'status'                     => ClaimModel::STATUS_REJECTED,
            'rejectionReason'            => $rejectionReason,
            'rejectionReasonDescription' => $rejectionReasonDescription
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setStatusAccepted(int $claimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'status' => ClaimModel::STATUS_ACCEPTED
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setStatusDuplicate(int $claimId, int $duplicateOfClaimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'status' => ClaimModel::STATUS_DUPLICATE,
            'duplicateOfClaimId' => $duplicateOfClaimId
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setStatusWithdrawn(int $claimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'status' => ClaimModel::STATUS_WITHDRAWN
        ]);

        return $this->createDataModel($claimArray);
    }

    public function changeClaimOutcome(int $claimId, string $reason)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'status' => ClaimModel::STATUS_IN_PROGRESS,
            'reason' => $reason
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setOutcomeLetterSent(int $claimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'outcomeLetterSent' => true
        ]);

        return $this->createDataModel($claimArray);
    }

    public function setOutcomePhoneCalled(int $claimId)
    {
        $claimArray = $this->getApiClient()->httpPatch("/v1/claim/$claimId", [
            'outcomePhoneCalled' => true
        ]);

        return $this->createDataModel($claimArray);
    }

    /**
     * @param ClaimModel $claim
     * @param PoaModel $poa
     */
    private function updatePoaCaseNumberVerification(ClaimModel $claim, PoaModel $poa)
    {
        if ($claim->getApplication()->hasCaseNumber()) {
            $poaCaseNumber = $claim->getApplication()->getCaseNumber()->getPoaCaseNumber();
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

    /**
     * @param array $searchParameters
     * @return array
     */
    private function getSearchQueryParameters(array $searchParameters): array
    {
        $page = isset($searchParameters['page']) ? $searchParameters['page'] : null;
        $pageSize = isset($searchParameters['pageSize']) ? $searchParameters['pageSize'] : null;
        $search = isset($searchParameters['search']) ? $searchParameters['search'] : null;
        $received = isset($searchParameters['received']) ? $searchParameters['received'] : null;
        $finished = isset($searchParameters['finished']) ? $searchParameters['finished'] : null;
        $assignedToFinishedById = isset($searchParameters['assignedToFinishedById'])
        && is_numeric($searchParameters['assignedToFinishedById']) ?
            (int)$searchParameters['assignedToFinishedById'] : null;
        $statuses = isset($searchParameters['statuses']) ? $searchParameters['statuses'] : null;
        $accountHash = isset($searchParameters['accountHash']) ? $searchParameters['accountHash'] : null;
        $poaCaseNumbers = isset($searchParameters['poaCaseNumbers']) ?
            explode(',', $searchParameters['poaCaseNumbers']) : null;
        $source = isset($searchParameters['source']) ? $searchParameters['source'] : null;
        $orderBy = isset($searchParameters['orderBy']) ? $searchParameters['orderBy'] : null;
        $sort = isset($searchParameters['sort']) ? $searchParameters['sort'] : null;

        $queryParameters = [];
        if ($page != null) {
            $queryParameters['page'] = $page;
        }
        if ($pageSize != null) {
            $queryParameters['pageSize'] = $pageSize;
        }
        if ($received != null) {
            $queryParameters['received'] = $received;
        }
        if ($finished != null) {
            $queryParameters['finished'] = $finished;
        }
        if ($search != null) {
            $queryParameters['search'] = $search;
        }
        if ($assignedToFinishedById != null) {
            $queryParameters['assignedToFinishedById'] = $assignedToFinishedById;
        }
        if ($statuses != null) {
            $queryParameters['statuses'] = $statuses;
        }
        if ($accountHash != null) {
            $queryParameters['accountHash'] = $accountHash;
        }
        if ($poaCaseNumbers != null) {
            $queryParameters['poaCaseNumbers'] = join(',', $poaCaseNumbers);
        }
        if ($source != null) {
            $queryParameters['source'] = $source;
        }
        if ($orderBy != null) {
            $queryParameters['orderBy'] = $orderBy;
        }
        if ($sort != null) {
            $queryParameters['sort'] = $sort;
        }
        return $queryParameters;
    }
}
