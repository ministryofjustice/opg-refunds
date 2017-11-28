<?php

namespace App\Service;

use DateTime;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Ingestion\Service\ApplicationIngestion;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\ClaimSummary as ClaimSummaryModel;
use Opg\Refunds\Caseworker\DataModel\Cases\ClaimSummaryPage;
use Opg\Refunds\Caseworker\DataModel\Cases\Note as NoteModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Entity\Cases\User as UserEntity;
use App\Entity\Cases\Note as NoteEntity;
use App\Entity\Cases\Poa as PoaEntity;
use App\Entity\Cases\Verification as VerificationEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Opg\Refunds\Caseworker\DataModel\RejectionReasonsFormatter;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Class Claim
 * @package App\Service
 */
class Claim
{
    use EntityToModelTrait;

    /**
     * @var EntityRepository
     */
    private $claimRepository;

    /**
     * @var EntityRepository
     */
    private $poaRepository;

    /**
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ApplicationIngestion
     */
    private $applicationIngestionService;

    /**
     * Claim constructor
     *
     * @param EntityManager $entityManager
     * @param ApplicationIngestion $applicationIngestionService
     */
    public function __construct(EntityManager $entityManager, ApplicationIngestion $applicationIngestionService)
    {
        $this->claimRepository = $entityManager->getRepository(ClaimEntity::class);
        $this->poaRepository = $entityManager->getRepository(PoaEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->applicationIngestionService = $applicationIngestionService;
    }

    /**
     * Search all claims
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
    public function search(int $page = null, int $pageSize = null, string $search = null, int $assignedToId = null, string $status = null, string $accountHash = null, string $orderBy = null, string $sort = null)
    {
        //TODO: Get proper migration running via cron job
        $this->applicationIngestionService->ingestAllApplication();

        if ($page === null) {
            $page = 1;
        }

        if ($pageSize === null) {
            $pageSize = 25;
        } elseif ($pageSize > 50) {
            $pageSize = 50;
        }

        $join = '';
        $whereClauses = [];
        $parameters = [];

        if (isset($search)) {
            $donorName = $search;
            $claimId = IdentFormatter::parseId($search);

            if ($claimId !== false) {
                $whereClauses[] = 'c.id = :claimId';
                $parameters['claimId'] = $claimId;
            } else {
                $whereClauses[] = 'LOWER(c.donorName) LIKE LOWER(:donorName)';
                $parameters['donorName'] = "%{$donorName}%";
            }
        }

        if (isset($assignedToId)) {
            $join = ' JOIN c.assignedTo u';
            $whereClauses[] = 'u.id = :assignedToId';
            $parameters['assignedToId'] = $assignedToId;
        }

        if (isset($status)) {
            $whereClauses[] = 'c.status = :status';
            $parameters['status'] = $status;
        }

        if (isset($accountHash)) {
            $whereClauses[] = 'c.accountHash = :accountHash';
            $parameters['accountHash'] = $accountHash;
        }

        $offset = ($page - 1) * $pageSize;

        // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/pagination.html
        $dql = 'SELECT c FROM App\Entity\Cases\Claim c' . $join;
        if (count($whereClauses) > 0) {
            $dql .= ' WHERE ' . join(' AND ', $whereClauses);
        }

        if (isset($orderBy)) {
            if ($orderBy === 'donor') {
                $dql .= ' ORDER BY c.donorName ';
            } elseif ($orderBy === 'received') {
                $dql .= ' ORDER BY c.receivedDateTime ';
            } elseif ($orderBy === 'modified') {
                $dql .= ' ORDER BY c.updatedDateTime ';
            } elseif ($orderBy === 'finished') {
                $dql .= ' ORDER BY c.finishedDateTime ';
            } elseif ($orderBy === 'status') {
                $dql .= ' ORDER BY c.status ';
            }

            $dql .= strtoupper($sort ?: 'asc');
        }

        $query = $this->entityManager->createQuery($dql)
            ->setParameters($parameters)
            ->setFirstResult($offset)
            ->setMaxResults($pageSize);

        $paginator = new Paginator($query, true);

        $total = count($paginator);
        $pageCount = ceil($total/$pageSize);

        $claimSummaries = [];

        foreach ($paginator as $claim) {
            $claimSummaries[] = $this->translateToDataModel($claim, [], ClaimSummaryModel::class);
        }

        $claimSummaryPage = new ClaimSummaryPage();
        $claimSummaryPage
            ->setPage($page)
            ->setPageSize($pageSize)
            ->setPageCount($pageCount)
            ->setTotal($total)
            ->setClaimSummaries($claimSummaries);

        return $claimSummaryPage;
    }

    /**
     * Get one claim
     *
     * @param int $claimId
     * @param int $userId
     * @return ClaimModel
     */
    public function get(int $claimId, int $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        return $this->getClaimModel($userId, $claim);
    }

    /**
     * @param int $userId
     * @return array with one element, 'assignedClaimId'
     * which will be the assigned claim id if one was successfully assigned to the user or zero if not
     * @throws Exception
     */
    public function assignNextClaim(int $userId)
    {
        //TODO: Get proper migration running via cron job
        $this->applicationIngestionService->ingestApplication();

        $user = $this->getUser($userId);

        //Using SQL directly to update claim in single atomic call to prevent race conditions
        $statement = $this->entityManager->getConnection()->executeQuery(
            'UPDATE claim SET assigned_to_id = ?, assigned_datetime = NOW(), updated_datetime = NOW(), status = ? WHERE id = (SELECT id FROM claim WHERE assigned_to_id IS NULL AND status = ? ORDER BY received_datetime ASC LIMIT 1) RETURNING id',
            [$user->getId(), ClaimModel::STATUS_IN_PROGRESS, ClaimModel::STATUS_PENDING]
        );

        $result = $statement->fetchAll();
        $updateCount = count($result);

        if ($updateCount > 1) {
            throw new Exception(
                "Assigning next claim updated $updateCount rows! It should only update one or zero rows"
            );
        }

        $assignedClaimId = 0;

        if ($updateCount === 1) {
            $assignedClaimId = $result[0]['id'];

            $this->addNote(
                $assignedClaimId,
                $userId,
                NoteModel::TYPE_CLAIM_IN_PROGRESS,
                "Caseworker has begun to process this claim"
            );
        }

        return ['assignedClaimId' => $assignedClaimId];
    }

    /**
     * Assign a claim to a user
     *
     * @param int $claimId
     * @param int $userId
     * @param int $assignToUserId
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function assignClaim(int $claimId, int $userId, int $assignToUserId, string $reason)
    {
        $claim = $this->getClaimEntity($claimId);
        $claimModel = $this->getClaimModel($userId, $claim);

        if ($claim->getStatus() !== ClaimModel::STATUS_PENDING && !$claimModel->canReassignClaim()) {
            throw new Exception('You cannot (re)assign this claim', 400);
        }

        $assignedTo = $this->getUser($assignToUserId);

        $claim->setUpdatedDateTime(new DateTime());
        $originalAssignedTo = $claim->getAssignedTo();
        $claim->setAssignedTo($assignedTo);
        $claim->setAssignedDateTime(new DateTime());

        if ($claim->getStatus() === ClaimModel::STATUS_PENDING) {
            // Explicit assignment
            $claim->setStatus(ClaimModel::STATUS_IN_PROGRESS);

            $this->addNote(
                $claim->getId(),
                $userId,
                NoteModel::TYPE_CLAIM_IN_PROGRESS,
                "Caseworker has begun to process this claim"
            );
        } elseif ($claim->getStatus() === ClaimModel::STATUS_IN_PROGRESS) {
            // Reassignment
            $message = "Claim has been reassigned from {$originalAssignedTo->getName()} to {$assignedTo->getName()}";

            if (!empty($reason)) {
                $message .= " due to '{$reason}'";
            }

            $this->addNote(
                $claim->getId(),
                $userId,
                NoteModel::TYPE_CLAIM_REASSIGNED,
                $message
            );
        }

        return [
            'assignedClaimId' => $claim->getId(),
            'assignedToName'  => $assignedTo->getName()
        ];
    }

    /**
     * Removes the assigned user from the claim making it available for another caseworker
     *
     * @param int $claimId
     * @param int $userId
     */
    public function unAssignClaim(int $claimId, int $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $claim->setStatus(ClaimModel::STATUS_PENDING);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_PENDING,
            'Caseworker returned claim to pool'
        );
    }

    /**
     * Removes the assigned user from the claim to make it available for another caseworker
     *
     * @param int $claimId
     * @param int $userId
     */
    public function removeClaim(int $claimId, int $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        $assignedUser = $claim->getAssignedTo();

        $claim->setStatus(ClaimModel::STATUS_PENDING);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_PENDING,
            "Claim has been returned to the pool because the assigned caseworker ({$assignedUser->getName()}) was deleted"
        );
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param bool $noSiriusPoas
     */
    public function setNoSiriusPoas(int $claimId, int $userId, bool $noSiriusPoas)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $claim->setNoSiriusPoas($noSiriusPoas);
        $claim->setUpdatedDateTime(new DateTime());

        if ($noSiriusPoas) {
            $this->addNote(
                $claimId,
                $userId,
                NoteModel::TYPE_NO_SIRIUS_POAS,
                "Caseworker confirmed that there were no eligible Sirius POAs for this claim"
            );
        } else {
            $this->addNote(
                $claimId,
                $userId,
                NoteModel::TYPE_SIRIUS_POAS_FOUND,
                "Caseworker has found a Sirius POA for this claim"
            );
        }
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param bool $noMerisPoas
     */
    public function setNoMerisPoas(int $claimId, int $userId, bool $noMerisPoas)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $claim->setNoMerisPoas($noMerisPoas);
        $claim->setUpdatedDateTime(new DateTime());

        if ($noMerisPoas) {
            $this->addNote(
                $claimId,
                $userId,
                NoteModel::TYPE_NO_MERIS_POAS,
                "Caseworker confirmed that there were no eligible Meris POAs for this claim"
            );
        } else {
            $this->addNote(
                $claimId,
                $userId,
                NoteModel::TYPE_MERIS_POAS_FOUND,
                "Caseworker has found a Meris POA for this claim"
            );
        }
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param string $type
     * @param string $message
     * @return NoteModel
     */
    public function addNote(int $claimId, int $userId, string $type, string $message)
    {
        $claim = $this->getClaimEntity($claimId);

        $user = $this->getUser($userId);

        $note = new NoteEntity($type, $message, $claim, $user);

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        /** @var NoteModel $noteModel */
        $noteModel = $this->translateToDataModel($note);
        return $noteModel;
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param PoaModel $poaModel
     * @return ClaimModel
     */
    public function addPoa(int $claimId, int $userId, PoaModel $poaModel)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $claim->setUpdatedDateTime(new DateTime());

        $poa = new PoaEntity($poaModel->getSystem(), $poaModel->getCaseNumber(), $poaModel->getReceivedDate(), $poaModel->getOriginalPaymentAmount(), $claim);
        $this->entityManager->persist($poa);

        if ($poaModel->getVerifications() !== null) {
            foreach ($poaModel->getVerifications() as $verificationModel) {
                $verification = new VerificationEntity($verificationModel->getType(), $verificationModel->isPasses(), $poa);
                $this->entityManager->persist($verification);
            }
        }

        $this->flushPoaChanges($poaModel);

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_POA_ADDED,
            "Power of attorney with case number {$this->getCaseNumberNote($poaModel)} was successfully added to this claim"
        );

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    /**
     * @param int $claimId
     * @param int $poaId
     * @param int $userId
     * @param PoaModel $poaModel
     * @return ClaimModel
     */
    public function editPoa(int $claimId, int $poaId, int $userId, PoaModel $poaModel)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $claim->setUpdatedDateTime(new DateTime());

        /** @var PoaEntity $poa */
        $poa = $this->poaRepository->findOneBy([
            'id' => $poaId,
        ]);

        $poa->setCaseNumber($poaModel->getCaseNumber());
        $poa->setReceivedDate($poaModel->getReceivedDate());
        $poa->setOriginalPaymentAmount($poaModel->getOriginalPaymentAmount());

        //Remove any that are no longer present on supplied document
        if ($poa->getVerifications() !== null) {
            foreach ($poa->getVerifications() as $verificationEntity) {
                $remove = true;

                if ($poaModel->getVerifications() !== null) {
                    foreach ($poaModel->getVerifications() as $verificationModel) {
                        if ($verificationEntity->getType() === $verificationModel->getType()) {
                            $remove = false;
                            break;
                        }
                    }
                }

                if ($remove) {
                    $this->entityManager->remove($verificationEntity);
                }
            }
        }

        //Update existing verifications
        if ($poaModel->getVerifications() !== null) {
            foreach ($poaModel->getVerifications() as $verificationModel) {
                //Default id to 0 so this can be detected as a new verification later
                $verificationModel->setId(0);

                if ($poa->getVerifications() !== null) {
                    foreach ($poa->getVerifications() as $verificationEntity) {
                        if ($verificationModel->getType() === $verificationEntity->getType()) {
                            $verificationEntity->setPasses($verificationModel->isPasses());
                            $verificationModel->setId($verificationEntity->getId());
                        }
                    }
                }

                if ($verificationModel->getId() === 0) {
                    //New verification so add
                    $verification = new VerificationEntity($verificationModel->getType(), $verificationModel->isPasses(), $poa);
                    $this->entityManager->persist($verification);
                }
            }
        }

        $this->flushPoaChanges($poaModel);

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_POA_EDITED,
            "Power of attorney with case number {$this->getCaseNumberNote($poaModel)} was successfully edited"
        );

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    /**
     * @param $claimId
     * @param $poaId
     * @param $userId
     * @return ClaimModel
     */
    public function deletePoa($claimId, $poaId, $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $claim->setUpdatedDateTime(new DateTime());

        /** @var PoaEntity $poa */
        $poa = $this->poaRepository->findOneBy([
            'id' => $poaId,
        ]);

        $this->entityManager->remove($poa);
        $this->entityManager->flush();

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_POA_DELETED,
            "Power of attorney with case number {$poa->getCaseNumber()} was successfully deleted"
        );

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    /**
     * @param $claimId
     * @param $userId
     */
    public function setStatusAccepted($claimId, $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_ACCEPTED);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedBy($user);
        $claim->setFinishedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_ACCEPTED,
            "Caseworker accepted the claim and it will be processed in the next refund run"
        );
    }

    /**
     * @param $claimId
     * @param $userId
     * @param $rejectionReason
     * @param $rejectionReasonDescription
     */
    public function setStatusRejected($claimId, $userId, $rejectionReason, $rejectionReasonDescription)
    {
        $claim = $this->getClaimEntity($claimId);

        $this->checkCanEdit($claim, $userId);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_REJECTED);
        $claim->setRejectionReason($rejectionReason);
        $claim->setRejectionReasonDescription($rejectionReasonDescription);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedBy($user);
        $claim->setFinishedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $rejectionReasonText = RejectionReasonsFormatter::getRejectionReasonText($rejectionReason);
        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_REJECTED,
            "Caseworker rejected the claim due to '{$rejectionReasonText}'"
        );
    }

    /**
     * @param $claimId
     * @param $userId
     * @param $reason
     * @throws Exception
     */
    public function setStatusInProgress($claimId, $userId, $reason)
    {
        $claim = $this->getClaimEntity($claimId);
        $claimModel = $this->getClaimModel($userId, $claim);

        if (!$claimModel->canChangeOutcome() || $claim->getFinishedBy() === null) {
            throw new Exception('You cannot set this claim\'s status back to pending', 400);
        }

        $finishedBy = $claim->getFinishedBy();

        $claim->setStatus(ClaimModel::STATUS_IN_PROGRESS);
        $claim->setRejectionReason(null);
        $claim->setRejectionReasonDescription(null);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedBy(null);
        $claim->setFinishedDateTime(null);
        $claim->setAssignedTo($finishedBy);
        $claim->setAssignedDateTime(new DateTime());

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_OUTCOME_CHANGED,
            "Administrator changed the claim outcome due to: '{$reason}'. Claim was reassigned to {$finishedBy->getName()}"
        );
    }

    /**
     * @param $claimId
     * @param $userId
     * @param int $duplicateOfClaimId
     * @throws Exception
     */
    public function setStatusDuplicate($claimId, $userId, int $duplicateOfClaimId)
    {
        $claim = $this->getClaimEntity($claimId);
        $claimModel = $this->getClaimModel($userId, $claim);

        if (!$claimModel->canResolveAsDuplicate()) {
            throw new Exception('You cannot resolve this claim as a duplicate', 400);
        }

        $duplicateOfClaim = $this->getClaimEntity($duplicateOfClaimId);

        if ($duplicateOfClaim === null) {
            throw new Exception('Supplied duplicate claim id does not reference a valid claim', 400);
        }

        $duplicateOf = $claim->getDuplicateOf();
        $duplicateOf->add($duplicateOfClaim);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_DUPLICATE);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedBy($user);
        $claim->setFinishedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $duplicateOfReferenceNumber = IdentFormatter::format($duplicateOfClaim->getId());

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_DUPLICATE,
            "Caseworker marked the claim as a duplicate of {$duplicateOfReferenceNumber}"
        );
    }

    /**
     * @param $claimId
     * @return ClaimEntity
     */
    public function getClaimEntity($claimId): ClaimEntity
    {
        /** @var ClaimEntity $claim */
        $claim = $this->claimRepository->findOneBy([
            'id' => $claimId,
        ]);
        return $claim;
    }

    /**
     * @param PoaModel $poaModel
     * @throws DriverException
     * @throws Exception
     */
    public function flushPoaChanges(PoaModel $poaModel)
    {
        try {
            $this->entityManager->flush();
        } catch (DriverException $ex) {
            if ($ex->getErrorCode() === 7) {
                //Duplicate case number
                throw new Exception("Case number {$poaModel->getCaseNumber()} is already registered with another claim", 400);
            }
            throw $ex;
        }
    }

    /**
     * @param int $accountHashCount
     * @param ClaimEntity $claim
     * @param int $userId
     * @return array
     */
    private function getClaimModelToEntityMappings($accountHashCount, ClaimEntity $claim, int $userId): array
    {
        return [
            'AccountHashCount' => function () use ($accountHashCount) {
                return $accountHashCount;
            },
            'ReadOnly' => function () use ($claim, $userId) {
                return $this->isReadOnly($claim, $userId);
            }
        ];
    }

    /**
     * Check if a given claim should be considered read only for the user ID provided
     *
     * @param ClaimEntity $claim
     * @param int $userId
     * @return bool
     */
    private function isReadOnly(ClaimEntity $claim, int $userId)
    {
        // Deliberately not checking $claim->getAssignedTo() !== null as in progress claims should always be assigned
        return $claim->getStatus() !== ClaimModel::STATUS_IN_PROGRESS || $claim->getAssignedTo()->getId() !== $userId;
    }

    /**
     * Check if a user can edit a specific claim
     *
     * @param $claim
     * @param $userId
     * @throws Exception
     */
    private function checkCanEdit($claim, $userId)
    {
        if ($this->isReadOnly($claim, $userId)) {
            throw new Exception('You cannot edit this claim', 400);
        }
    }

    /**
     * @param PoaModel $poaModel
     * @return string
     */
    public function getCaseNumberNote(PoaModel $poaModel): string
    {
        return $poaModel->getCaseNumber() . ($poaModel->isComplete() ? '' : ' (incomplete)');
    }

    /**
     * @param int $userId
     * @return UserEntity
     */
    public function getUser(int $userId)
    {
        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);
        return $user;
    }

    /**
     * @param int $userId
     * @param ClaimEntity $claim
     * @return ClaimModel
     */
    private function getClaimModel(int $userId, ClaimEntity $claim): ClaimModel
    {
        $accountHashCount = null;

        if ($claim->getAccountHash() !== null) {
            $dql = 'SELECT COUNT(c.id) AS account_hash_count FROM App\Entity\Cases\Claim c WHERE c.accountHash = ?1';
            $accountHashCount = $this->entityManager->createQuery($dql)
                ->setParameter(1, $claim->getAccountHash())
                ->getSingleScalarResult();
        }

        /** @var ClaimModel $claimModel */
        $claimModelToEntityMappings = $this->getClaimModelToEntityMappings($accountHashCount, $claim, $userId);
        $claimModel = $this->translateToDataModel($claim, $claimModelToEntityMappings);

        return $claimModel;
    }
}
