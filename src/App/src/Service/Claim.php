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
        $this->entityManager = $entityManager;
        $this->applicationIngestionService = $applicationIngestionService;
    }

    /**
     * Get all claims
     *
     * @param int|null $page
     * @param int|null $pageSize
     * @param string|null $donorName
     * @param int|null $assignedToId
     * @param string|null $status
     * @param string|null $accountHash
     * @return ClaimSummaryPage
     */
    public function search($page, $pageSize, $donorName, $assignedToId, $status, $accountHash)
    {
        //TODO: Get proper migration running via cron job
        $this->applicationIngestionService->ingestAllApplication();

        if ($page === null) {
            $page = 1;
        }

        if ($pageSize === null) {
            $pageSize = 10;
        } elseif ($pageSize > 50) {
            $pageSize = 50;
        }

        $join = '';
        $whereClauses = [];
        $parameters = [];

        if (isset($donorName)) {
            $whereClauses[] = 'LOWER(c.donorName) LIKE LOWER(:donorName)';
            $parameters['donorName'] = "%{$donorName}%";
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
        $dql .= ' ORDER BY c.receivedDateTime ASC';
        $query = $this->entityManager->createQuery($dql)
            ->setParameters($parameters)
            ->setFirstResult($offset)
            ->setMaxResults($pageSize);

        $paginator = new Paginator($query, true);

        $total = count($paginator);
        $pageCount = ceil($total/$pageSize);

        $claimSummaries = [];

        foreach ($paginator as $claim) {
            $claimSummaries[] = $this->translateToDataModel($claim, ClaimSummaryModel::class);
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
     * @param $claimId
     * @return ClaimModel
     */
    public function get($claimId)
    {
        $claim = $this->getClaimEntity($claimId);

        $dql = 'SELECT COUNT(c.id) AS account_hash_count FROM App\Entity\Cases\Claim c WHERE c.accountHash = ?1';
        $accountHashCount = $this->entityManager->createQuery($dql)
            ->setParameter(1, $claim->getAccountHash())
            ->getSingleScalarResult();
        $claim->setAccountHashCount($accountHashCount);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
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

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);

        //Using SQL directly to update claim in single atomic call to prevent race conditions
        $statement = $this->entityManager->getConnection()->executeQuery(
            'UPDATE claim SET assigned_to_id = ?, assigned_datetime = NOW(), updated_datetime = NOW(), status = ? WHERE id = (SELECT id FROM claim WHERE assigned_to_id IS NULL AND status = ? ORDER BY received_datetime ASC LIMIT 1) RETURNING id',
            [$user->getId(), ClaimModel::STATUS_IN_PROGRESS, ClaimModel::STATUS_NEW]
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
                'Claim started by caseworker',
                "Caseworker has begun to process this claim"
            );
        }

        return ['assignedClaimId' => $assignedClaimId];
    }

    public function setNoSiriusPoas(int $claimId, int $userId, bool $noSiriusPoas)
    {
        $claim = $this->getClaimEntity($claimId);

        $claim->setNoSiriusPoas($noSiriusPoas);
        $claim->setUpdatedDateTime(new DateTime());

        if ($noSiriusPoas) {
            $this->addNote(
                $claimId,
                $userId,
                'No Sirius POAs',
                "Caseworker confirmed that they could not find any Sirius POAs for this claim"
            );
        } else {
            $this->addNote(
                $claimId,
                $userId,
                'Sirius POA found',
                "Caseworker has found a Sirius POA for this claim"
            );
        }
    }

    public function setNoMerisPoas(int $claimId, int $userId, bool $noMerisPoas)
    {
        $claim = $this->getClaimEntity($claimId);

        $claim->setNoMerisPoas($noMerisPoas);
        $claim->setUpdatedDateTime(new DateTime());

        if ($noMerisPoas) {
            $this->addNote(
                $claimId,
                $userId,
                'No Meris POAs',
                "Caseworker confirmed that they could not find any Meris POAs for this claim"
            );
        } else {
            $this->addNote(
                $claimId,
                $userId,
                'Meris POA found',
                "Caseworker has found a Meris POA for this claim"
            );
        }
    }

    /**
     * @param int $claimId
     * @param int $userId
     * @param string $title
     * @param string $message
     * @return NoteModel
     */
    public function addNote(int $claimId, int $userId, string $title, string $message)
    {
        $claim = $this->getClaimEntity($claimId);

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);

        $note = new NoteEntity($title, $message, $claim, $user);

        $this->entityManager->persist($note);
        $this->entityManager->flush();

        /** @var NoteModel $noteModel */
        $noteModel = $this->translateToDataModel($note);
        return $noteModel;
    }

    public function addPoa(int $claimId, int $userId, PoaModel $poaModel)
    {
        $claim = $this->getClaimEntity($claimId);
        $claim->setUpdatedDateTime(new DateTime());

        $poa = new PoaEntity($poaModel->getSystem(), $poaModel->getCaseNumber(), $poaModel->getReceivedDate(), $poaModel->getOriginalPaymentAmount(), $claim);
        $this->entityManager->persist($poa);

        foreach ($poaModel->getVerifications() as $verificationModel) {
            $verification = new VerificationEntity($verificationModel->getType(), $verificationModel->isPasses(), $poa);
            $this->entityManager->persist($verification);
        }

        $this->flushPoaChanges($poaModel);

        $this->addNote($claimId, $userId, 'POA added', "Power of attorney with case number {$poa->getCaseNumber()} was successfully added to this claim, changing verification details");

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    public function editPoa(int $claimId, int $poaId, int $userId, PoaModel $poaModel)
    {
        $claim = $this->getClaimEntity($claimId);
        $claim->setUpdatedDateTime(new DateTime());

        /** @var PoaEntity $poa */
        $poa = $this->poaRepository->findOneBy([
            'id' => $poaId,
        ]);

        $poa->setCaseNumber($poaModel->getCaseNumber());
        $poa->setReceivedDate($poaModel->getReceivedDate());
        $poa->setOriginalPaymentAmount($poaModel->getOriginalPaymentAmount());

        //Remove any that are no longer present on supplied document
        foreach ($poa->getVerifications() as $verificationEntity) {
            $remove = true;

            foreach ($poaModel->getVerifications() as $verificationModel) {
                if ($verificationEntity->getType() === $verificationModel->getType()) {
                    $remove = false;
                    break;
                }
            }

            if ($remove) {
                $this->entityManager->remove($verificationEntity);
            }
        }

        //Update existing verifications
        foreach ($poaModel->getVerifications() as $verificationModel) {
            //Default id to 0 so this can be detected as a new verification later
            $verificationModel->setId(0);

            foreach ($poa->getVerifications() as $verificationEntity) {
                if ($verificationModel->getType() === $verificationEntity->getType()) {
                    $verificationEntity->setPasses($verificationModel->isPasses());
                    $verificationModel->setId($verificationEntity->getId());
                }
            }

            if ($verificationModel->getId() === 0) {
                //New verification so add
                $verification = new VerificationEntity($verificationModel->getType(), $verificationModel->isPasses(), $poa);
                $this->entityManager->persist($verification);
            }
        }

        $this->flushPoaChanges($poaModel);

        $this->addNote($claimId, $userId, 'POA edited', "Power of attorney with case number {$poa->getCaseNumber()} was successfully edited, changing verification details");

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    public function deletePoa($claimId, $poaId, $userId)
    {
        $claim = $this->getClaimEntity($claimId);
        $claim->setUpdatedDateTime(new DateTime());

        /** @var PoaEntity $poa */
        $poa = $this->poaRepository->findOneBy([
            'id' => $poaId,
        ]);

        $this->entityManager->remove($poa);
        $this->entityManager->flush();

        $this->addNote($claimId, $userId, 'POA delete', "Power of attorney with case number {$poa->getCaseNumber()} was successfully deleted, changing verification details");

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
    }

    public function setStatusAccepted($claimId, $userId)
    {
        $claim = $this->getClaimEntity($claimId);

        $claim->setStatus(ClaimModel::STATUS_ACCEPTED);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $this->addNote(
            $claimId,
            $userId,
            'Claim accepted',
            "Caseworker accepted the claim and it will be processed in the next refund run"
        );
    }

    public function setStatusRejected($claimId, $userId, $rejectionReason, $rejectionReasonDescription)
    {
        $claim = $this->getClaimEntity($claimId);

        $claim->setStatus(ClaimModel::STATUS_REJECTED);
        $claim->setRejectionReason($rejectionReason);
        $claim->setRejectionReasonDescription($rejectionReasonDescription);
        $claim->setUpdatedDateTime(new DateTime());
        $claim->setFinishedDateTime(new DateTime());
        $claim->setAssignedTo(null);
        $claim->setAssignedDateTime(null);

        $this->addNote(
            $claimId,
            $userId,
            'Claim rejected',
            "Caseworker rejected the claim due to {$rejectionReason}"
        );
    }

    /**
     * @param $claimId
     * @return ClaimEntity
     */
    private function getClaimEntity($claimId): ClaimEntity
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
}
