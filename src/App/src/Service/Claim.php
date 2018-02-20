<?php

namespace App\Service;

use App\Exception\AlreadyExistsException;
use App\Exception\InvalidInputException;
use App\Exception\NotFoundException;
use DateInterval;
use DateTime;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
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
use App\Service\Account as AccountService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Opg\Refunds\Caseworker\DataModel\IdentFormatter;
use Opg\Refunds\Caseworker\DataModel\RejectionReasonsFormatter;

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
     * @var AccountService
     */
    private $accountService;

    /**
     * Claim constructor
     *
     * @param EntityManager $entityManager
     * @param Account $accountService
     */
    public function __construct(EntityManager $entityManager, AccountService $accountService)
    {
        $this->claimRepository = $entityManager->getRepository(ClaimEntity::class);
        $this->poaRepository = $entityManager->getRepository(PoaEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->accountService = $accountService;
    }

    /**
     * Search all claims
     *
     * @param array $queryParameters
     * @return ClaimSummaryPage
     */
    public function search(array $queryParameters)
    {
        $page = isset($queryParameters['page']) ? $queryParameters['page'] : null;
        $pageSize = isset($queryParameters['pageSize']) ? $queryParameters['pageSize'] : null;

        if ($page === null) {
            $page = 1;
        }

        if ($pageSize === null) {
            $pageSize = 25;
        } elseif ($pageSize > 50) {
            $pageSize = 50;
        }

        $queryBuilder = $this->getSearchQueryBuilder($queryParameters);

        $offset = ($page - 1) * $pageSize;

        // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/pagination.html
        $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($pageSize);

        $paginator = new Paginator($queryBuilder, true);

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
     * Returns all claim summaries that match the search with no pagination.
     * Should only be used to populate a spreadsheet
     *
     * @param array $queryParameters
     * @return ClaimSummaryModel[]
     */
    public function searchAll(array $queryParameters)
    {
        $queryBuilder = $this->getSearchQueryBuilder($queryParameters);

        $claimSummaries = [];

        foreach ($queryBuilder->getQuery()->getResult() as $claim) {
            $claimSummaries[] = $this->translateToDataModel($claim, [], ClaimSummaryModel::class);
        }

        return $claimSummaries;
    }

    /**
     * Get one claim
     *
     * @param int $claimId
     * @param int $userId
     * @return ClaimModel
     * @throws NotFoundException
     */
    public function get(int $claimId, int $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        if ($claim === null) {
            throw new NotFoundException('Claim not found');
        }

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
     * @throws InvalidInputException
     */
    public function assignClaim(int $claimId, int $userId, int $assignToUserId, string $reason)
    {
        $claim = $this->getClaimEntity($claimId);
        $originalClaim = clone $claim;

        $claimModel = $this->getClaimModel($userId, $claim);

        $assignedTo = $this->getUser($assignToUserId);

        $originalAssignedTo = $claim->getAssignedTo();
        $claim->setAssignedTo($assignedTo);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim != $originalClaim) {
            //Changed

            if ($claim->getStatus() !== ClaimModel::STATUS_PENDING && !$claimModel->canReassignClaim()) {
                throw new InvalidInputException('You cannot (re)assign this claim');
            }

            $claim->setUpdatedDateTime(new DateTime());
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
        $originalClaim = clone $claim;

        $claim->setStatus(ClaimModel::STATUS_PENDING);
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        $this->checkCanEdit($originalClaim, $userId);

        $claim->setUpdatedDateTime(new DateTime());

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
        $originalClaim = clone $claim;

        $claim->setStatus(ClaimModel::STATUS_PENDING);
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        $assignedUser = $claim->getAssignedTo();

        $claim->setUpdatedDateTime(new DateTime());

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
        $originalClaim = clone $claim;

        $claim->setNoSiriusPoas($noSiriusPoas);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        $this->checkCanEdit($originalClaim, $userId);

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
        $originalClaim = clone $claim;

        $claim->setNoMerisPoas($noMerisPoas);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        $this->checkCanEdit($originalClaim, $userId);

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

        if ($type === NoteModel::TYPE_USER) {
            $claimModel = $this->getClaimModel($userId, $claim);
            
            $notes = $claimModel->getNotes();

            /** @var NoteModel $firstNote */
            $firstNote = reset($notes);
            if ($firstNote instanceof NoteModel && $firstNote->getType() === $type && $firstNote->getMessage() === $message
                && $firstNote->getUserId() === $userId) {
                //Note has already been added
                return $firstNote;
            }
        }

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

        /** @var PoaEntity $poa */
        $poa = $this->poaRepository->findOneBy([
            'claim' => $claim,
            'caseNumber' => $poaModel->getCaseNumber()
        ]);

        if ($poa === null) {
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
        }

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

        /** @var PoaModel $originalPoaModel */
        $originalPoaModel = $poa->getAsDataModel();

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

        /** @var PoaEntity $updatedPoa */
        $updatedPoa = $this->poaRepository->findOneBy([
            'id' => $poaId,
        ]);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($originalPoaModel != $updatedPoa->getAsDataModel()) {
            //Changed
            $this->addNote(
                $claimId,
                $userId,
                NoteModel::TYPE_POA_EDITED,
                "Power of attorney with case number {$this->getCaseNumberNote($poaModel)} was successfully edited"
            );

            $claim = $this->getClaimEntity($claimId);
        }

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

        /** @var PoaEntity $poa */
        $poa = $this->poaRepository->findOneBy([
            'id' => $poaId,
        ]);

        if ($poa instanceof PoaEntity) {
            $this->checkCanEdit($claim, $userId);

            $claim->setUpdatedDateTime(new DateTime());

            $this->entityManager->remove($poa);
            $this->entityManager->flush();

            $this->addNote(
                $claimId,
                $userId,
                NoteModel::TYPE_POA_DELETED,
                "Power of attorney with case number {$poa->getCaseNumber()} was successfully deleted"
            );

            $claim = $this->getClaimEntity($claimId);
        }

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
        $originalClaim = clone $claim;

        $claimModel = $this->getClaimModel($userId, $claim);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_ACCEPTED);
        $claim->setFinishedBy($user);
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedDateTime(new DateTime());

        $this->checkCanEdit($originalClaim, $userId);

        $this->resetPoaCaseNumbersRejectionCount($claim, $claimModel);

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
        $originalClaim = clone $claim;

        $claimModel = $this->getClaimModel($userId, $claim);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_REJECTED);
        $claim->setRejectionReason($rejectionReason);
        $claim->setRejectionReasonDescription($rejectionReasonDescription);
        $claim->setFinishedBy($user);
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedDateTime(new DateTime());

        $this->checkCanEdit($originalClaim, $userId);

        $this->incrementPoaCaseNumbersRejectionCount($claim, $claimModel);

        $rejectionReasonText = RejectionReasonsFormatter::getRejectionReasonText($rejectionReason);
        $message = "Caseworker rejected the claim due to '{$rejectionReasonText}'";

        if (empty($rejectionReasonDescription) === false) {
            $message .= " with description '{$rejectionReasonDescription}'";
        }

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_REJECTED,
            $message
        );
    }

    /**
     * @param $claimId
     * @param $userId
     * @param $reason
     * @throws InvalidInputException|AlreadyExistsException|DriverException
     */
    public function setStatusInProgress($claimId, $userId, $reason)
    {
        $claim = $this->getClaimEntity($claimId);
        $originalClaim = clone $claim;

        $claimModel = $this->getClaimModel($userId, $claim);

        $finishedBy = $claim->getFinishedBy();

        $claim->setStatus(ClaimModel::STATUS_IN_PROGRESS);
        $claim->setRejectionReason(null);
        $claim->setRejectionReasonDescription(null);
        $claim->setFinishedBy(null);
        $claim->setFinishedDateTime(null);
        $claim->setAssignedTo($finishedBy);
        $claim->getDuplicateOf()->clear();

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        if (!$claimModel->canChangeOutcome()) {
            throw new InvalidInputException('You cannot set this claim\'s status back to pending');
        }

        $claim->setUpdatedDateTime(new DateTime());
        $claim->setAssignedDateTime(new DateTime());

        $this->resetPoaCaseNumbersRejectionCount($claim, $claimModel);

        try {
            $this->entityManager->flush();
        } catch (DriverException $ex) {
            if ($ex->getErrorCode() === 7) {
                //Duplicate case number
                throw new AlreadyExistsException("Could not set this claim's status back to pending due to a poa case number duplication");
            }
            throw $ex;
        }

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
     * @throws InvalidInputException
     */
    public function setStatusDuplicate($claimId, $userId, int $duplicateOfClaimId)
    {
        $duplicateOfClaim = $this->getClaimEntity($duplicateOfClaimId);

        if ($duplicateOfClaim === null) {
            throw new InvalidInputException('Supplied duplicate claim id does not reference a valid claim');
        }

        $claim = $this->getClaimEntity($claimId);
        $originalClaim = clone $claim;

        $claimModel = $this->getClaimModel($userId, $claim);

        $duplicateOf = $claim->getDuplicateOf();
        $duplicateOf->add($duplicateOfClaim);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_DUPLICATE);
        $claim->setFinishedBy($user);
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        if (!$claimModel->canResolveAsDuplicate()) {
            throw new InvalidInputException('You cannot resolve this claim as a duplicate');
        }

        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedDateTime(new DateTime());

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
     * @param $userId
     * @throws InvalidInputException
     */
    public function setStatusWithdrawn($claimId, $userId)
    {
        $claim = $this->getClaimEntity($claimId);
        $originalClaim = clone $claim;

        $claimModel = $this->getClaimModel($userId, $claim);

        $user = $this->getUser($userId);

        $claim->setStatus(ClaimModel::STATUS_WITHDRAWN);
        $claim->setFinishedBy($user);
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        //Want simple comparison not identity comparison
        /** @noinspection PhpNonStrictObjectEqualityInspection */
        if ($claim == $originalClaim) {
            //No changes
            return;
        }

        if (!$claimModel->canWithdrawClaim()) {
            throw new InvalidInputException('You cannot withdraw this claim');
        }

        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedDateTime(new DateTime());

        $this->incrementPoaCaseNumbersRejectionCount($claim, $claimModel);

        $this->addNote(
            $claimId,
            $userId,
            NoteModel::TYPE_CLAIM_WITHDRAWN,
            "Administrator withdrew the claim on behalf of the claimant"
        );
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param bool $outcomeLetterSent
     */
    public function setOutcomeLetterSent(int $claimId, int $userId, bool $outcomeLetterSent)
    {
        $claim = $this->getClaimEntity($claimId);
        $claimModel = $this->getClaimModel($userId, $claim);

        if (!$claimModel->shouldSendLetter()) {
            throw new InvalidInputException('A letter should not have been sent to the claimant of this claim');
        }

        $claim->setOutcomeLetterSent($outcomeLetterSent);

        if ($outcomeLetterSent === true) {
            switch ($claimModel->getStatus()) {
                case ClaimModel::STATUS_DUPLICATE:
                    $noteType = NoteModel::TYPE_CLAIM_DUPLICATE_LETTER_SENT;
                    $message = 'Successfully sent duplicate letter to';
                    break;
                case ClaimModel::STATUS_REJECTED:
                    $noteType = NoteModel::TYPE_CLAIM_REJECTED_LETTER_SENT;
                    $message = 'Successfully sent rejection letter to';
                    break;
                case ClaimModel::STATUS_ACCEPTED:
                    $noteType = NoteModel::TYPE_CLAIM_ACCEPTED_LETTER_SENT;
                    $message = 'Successfully sent acceptance letter to';
                    break;
                default:
                    return;
            }

            $this->addNote(
                $claimId,
                $userId,
                $noteType,
                $message . PHP_EOL . PHP_EOL . $claimModel->getApplication()->getContact()->getAddress()
            );
        }
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param bool $outcomePhoneCalled
     */
    public function setOutcomePhoneCalled(int $claimId, int $userId, bool $outcomePhoneCalled)
    {
        $claim = $this->getClaimEntity($claimId);
        $claimModel = $this->getClaimModel($userId, $claim);

        if (!$claimModel->shouldPhone()) {
            throw new InvalidInputException('A phone call should not have been made to the claimant of this claim');
        }

        $claim->setOutcomePhoneCalled($outcomePhoneCalled);

        if ($outcomePhoneCalled === true) {
            $message = "Successfully phoned {$claimModel->getApplication()->getContact()->getPhone()} to inform claimant that their claim was ";

            switch ($claimModel->getStatus()) {
                case ClaimModel::STATUS_DUPLICATE:
                    $noteType = NoteModel::TYPE_CLAIM_DUPLICATE_PHONE_CALLED;
                    $message .= 'a duplicate';
                    break;
                case ClaimModel::STATUS_REJECTED:
                    $noteType = NoteModel::TYPE_CLAIM_REJECTED_PHONE_CALLED;
                    $message .= 'rejected';
                    break;
                case ClaimModel::STATUS_ACCEPTED:
                    $noteType = NoteModel::TYPE_CLAIM_ACCEPTED_PHONE_CALLED;
                    $message .= 'accepted';
                    break;
                default:
                    return;
            }

            $this->addNote(
                $claimId,
                $userId,
                $noteType,
                $message
            );
        }
    }

    /**
     * @param $claimId
     * @return ClaimEntity
     */
    public function getClaimEntity($claimId)
    {
        /** @var ClaimEntity $claim */
        $claim = $this->claimRepository->findOneBy([
            'id' => $claimId,
        ]);
        return $claim;
    }

    /**
     * @param PoaModel $poaModel
     * @throws AlreadyExistsException|DriverException
     */
    public function flushPoaChanges(PoaModel $poaModel)
    {
        try {
            $this->entityManager->flush();
        } catch (DriverException $ex) {
            if ($ex->getErrorCode() === 7) {
                //Duplicate case number
                throw new AlreadyExistsException("Case number {$poaModel->getCaseNumber()} is already registered with another claim");
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
     * @throws InvalidInputException
     */
    private function checkCanEdit($claim, $userId)
    {
        if ($this->isReadOnly($claim, $userId)) {
            throw new InvalidInputException('You cannot edit this claim');
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
            $queryBuilder = $this->entityManager->createQueryBuilder()
                ->select('COUNT(c.id)')
                ->from('Cases:Claim', 'c')
                ->where('c.accountHash = ?1');
            $accountHashCount = $queryBuilder->getQuery()
                ->setParameter(1, $claim->getAccountHash())
                ->getSingleScalarResult();
        }

        /** @var ClaimModel $claimModel */
        $claimModelToEntityMappings = $this->getClaimModelToEntityMappings($accountHashCount, $claim, $userId);
        $claimModel = $this->translateToDataModel($claim, $claimModelToEntityMappings);

        if ($this->accountService->isBuildingSociety($claim->getAccountHash()) === true) {
            $claimModel->getApplication()->getAccount()->setBuildingSociety(true);
            $claimModel->getApplication()->getAccount()->setInstitutionName(
                $this->accountService->getBuildingSocietyName($claim->getAccountHash())
            );
        }

        return $claimModel;
    }

    /**
     * @param ClaimEntity $claim
     * @param ClaimModel $claimModel
     */
    private function resetPoaCaseNumbersRejectionCount(ClaimEntity $claim, ClaimModel $claimModel)
    {
        if ($claimModel->hasPoas()) {
            foreach ($claim->getPoas() as $poa) {
                $poa->setCaseNumberRejectionCount(0);
            }
        }
    }

    /**
     * @param ClaimEntity $claim
     * @param ClaimModel $claimModel
     */
    private function incrementPoaCaseNumbersRejectionCount(ClaimEntity $claim, ClaimModel $claimModel)
    {
        if ($claimModel->hasPoas()) {
            $caseNumbers = [];

            foreach ($claim->getPoas() as $poa) {
                $caseNumbers[] = $poa->getCaseNumber();
            }

            $sql = 'SELECT case_number, max(case_number_rejection_count) FROM poa WHERE case_number IN (\'' . join('\', \'', $caseNumbers) . '\') GROUP BY case_number';

            $statement = $this->entityManager->getConnection()->executeQuery(
                $sql
            );

            $maxCaseNumbersRejectionCounts = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

            foreach ($claim->getPoas() as $poa) {
                if (isset($maxCaseNumbersRejectionCounts[$poa->getCaseNumber()])) {
                    // Subsequent rejections
                    $poa->setCaseNumberRejectionCount($maxCaseNumbersRejectionCounts[$poa->getCaseNumber()] + 1);
                } else {
                    // First rejection
                    $poa->setCaseNumberRejectionCount(1);
                }
            }
        }
    }

    /**
     * @param array $queryParameters
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSearchQueryBuilder(array $queryParameters): \Doctrine\ORM\QueryBuilder
    {
        $search = isset($queryParameters['search']) ? $queryParameters['search'] : null;
        $received = isset($queryParameters['received']) ? $queryParameters['received'] : null;
        $finished = isset($queryParameters['finished']) ? $queryParameters['finished'] : null;
        $assignedToFinishedById = isset($queryParameters['assignedToFinishedById'])
            ? $queryParameters['assignedToFinishedById'] : null;
        $statuses = isset($queryParameters['statuses']) ? explode(',', $queryParameters['statuses']) : null;
        $accountHash = isset($queryParameters['accountHash']) ? $queryParameters['accountHash'] : null;
        $poaCaseNumbers = isset($queryParameters['poaCaseNumbers'])
            ? explode(',', $queryParameters['poaCaseNumbers']) : null;
        $orderBy = isset($queryParameters['orderBy']) ? $queryParameters['orderBy'] : null;
        $sort = isset($queryParameters['sort']) ? $queryParameters['sort'] : null;

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from('Cases:Claim', 'c');

        $parameters = [];

        if (isset($search)) {
            $donorName = $search;
            $claimId = IdentFormatter::parseId($search);

            if ($claimId !== false) {
                $queryBuilder->andWhere('c.id = :claimId');
                $parameters['claimId'] = $claimId;
            } else {
                $queryBuilder->andWhere('LOWER(c.donorName) LIKE LOWER(:donorName)');
                $parameters['donorName'] = "%{$donorName}%";
            }
        }

        if (isset($received)) {
            $receivedRange = explode('-', $received);

            $receivedFrom = DateTime::createFromFormat('d/m/Y', $receivedRange[0]);
            if ($receivedFrom instanceof DateTime) {
                $receivedFrom->setTime(0, 0, 0);

                $receivedTo = count($receivedRange) > 1 ? DateTime::createFromFormat('d/m/Y', $receivedRange[1])
                    : (clone $receivedFrom);
                if ($receivedTo instanceof DateTime) {
                    $receivedTo->add(new DateInterval('P1D'));
                    $receivedTo->setTime(0, 0, 0);

                    $queryBuilder->andWhere('c.receivedDateTime > :receivedFrom AND c.receivedDateTime < :receivedTo');
                    $parameters['receivedFrom'] = $receivedFrom;
                    $parameters['receivedTo'] = $receivedTo;
                }
            }
        }

        if (isset($finished)) {
            $finishedRange = explode('-', $finished);

            $finishedFrom = DateTime::createFromFormat('d/m/Y', $finishedRange[0]);
            if ($finishedFrom instanceof DateTime) {
                $finishedFrom->setTime(0, 0, 0);

                $finishedTo = count($finishedRange) > 1 ? DateTime::createFromFormat('d/m/Y', $finishedRange[1])
                    : (clone $finishedFrom);
                if ($finishedTo instanceof DateTime) {
                    $finishedTo->add(new DateInterval('P1D'));
                    $finishedTo->setTime(0, 0, 0);

                    $queryBuilder->andWhere('c.finishedDateTime > :finishedFrom AND c.finishedDateTime < :finishedTo');
                    $parameters['finishedFrom'] = $finishedFrom;
                    $parameters['finishedTo'] = $finishedTo;
                }
            }
        }

        if (isset($assignedToFinishedById)) {
            $queryBuilder->addSelect('ua');
            $queryBuilder->addSelect('uf');
            $queryBuilder->leftJoin('c.assignedTo', 'ua');
            $queryBuilder->leftJoin('c.finishedBy', 'uf');
            $queryBuilder->andWhere('(ua.id = :assignedToFinishedById OR uf.id = :assignedToFinishedById)');
            $parameters['assignedToFinishedById'] = $assignedToFinishedById;
        }

        if (isset($statuses)) {
            $queryBuilder->andWhere('c.status IN (:statuses)');
            $parameters['statuses'] = $statuses;
        }

        if (isset($accountHash)) {
            $queryBuilder->andWhere('c.accountHash = :accountHash');
            $parameters['accountHash'] = $accountHash;
        }

        if (isset($poaCaseNumbers)) {
            $queryBuilder->leftJoin('c.poas', 'p');
            $queryBuilder->andWhere('p.caseNumber IN (:poaCaseNumbers)');
            $parameters['poaCaseNumbers'] = $poaCaseNumbers;
        }

        if (isset($orderBy)) {
            $sort = strtoupper($sort ?: 'asc');

            if ($orderBy === 'donor') {
                $queryBuilder->orderBy('c.donorName', $sort);
            } elseif ($orderBy === 'received') {
                $queryBuilder->orderBy('c.receivedDateTime', $sort);
            } elseif ($orderBy === 'modified') {
                $queryBuilder->orderBy('c.updatedDateTime', $sort);
            } elseif ($orderBy === 'finished') {
                $queryBuilder->orderBy('c.finishedDateTime', $sort);
            } elseif ($orderBy === 'status') {
                $queryBuilder->orderBy('c.status', $sort);
            }
        }

        $queryBuilder->setParameters($parameters);
        return $queryBuilder;
    }
}
