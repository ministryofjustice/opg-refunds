<?php

namespace App\Service;

use Exception;
use Ingestion\Service\DataMigration;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Log as LogModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use App\Entity\Cases\Claim as ClaimEntity;
use App\Entity\Cases\User as UserEntity;
use App\Entity\Cases\Log as LogEntity;
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
    private $userRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DataMigration
     */
    private $dataMigrationService;

    /**
     * Claim constructor
     *
     * @param EntityManager $entityManager
     * @param DataMigration $dataMigrationService
     */
    public function __construct(EntityManager $entityManager, DataMigration $dataMigrationService)
    {
        $this->claimRepository = $entityManager->getRepository(ClaimEntity::class);
        $this->userRepository = $entityManager->getRepository(UserEntity::class);
        $this->entityManager = $entityManager;
        $this->entityManager = $entityManager;
        $this->dataMigrationService = $dataMigrationService;
    }

    /**
     * Get all claims
     *
     * @return ClaimModel[]
     */
    public function getAll()
    {
        //TODO: Get proper migration running via cron job
        $this->dataMigrationService->migrateAll();

        /** @var ClaimEntity[] $claims */
        $claims = $this->claimRepository->findBy([]);

        return $this->translateToDataModelArray($claims);
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
        $this->dataMigrationService->migrateOne();

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

            $this->addLog(
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

        if ($noSiriusPoas) {
            $this->addLog(
                $claimId,
                $userId,
                'No Sirius POAs',
                "Caseworker confirmed that they could not find any Sirius POAs for this claim"
            );
        } else {
            $this->addLog(
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

        if ($noMerisPoas) {
            $this->addLog(
                $claimId,
                $userId,
                'No Meris POAs',
                "Caseworker confirmed that they could not find any Meris POAs for this claim"
            );
        } else {
            $this->addLog(
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
     * @return LogModel
     */
    public function addLog(int $claimId, int $userId, string $title, string $message)
    {
        $claim = $this->getClaimEntity($claimId);

        /** @var UserEntity $user */
        $user = $this->userRepository->findOneBy([
            'id' => $userId,
        ]);

        $log = new LogEntity($title, $message, $claim, $user);
        $claim->addLog($log);

        $this->entityManager->flush();

        /** @var LogModel $logModel */
        $logModel = $this->translateToDataModel($log);
        return $logModel;
    }

    public function addPoa(int $claimId, int $userId, PoaModel $poaModel)
    {
        $claim = $this->getClaimEntity($claimId);

        $poa = new PoaEntity($poaModel->getSystem(), $poaModel->getCaseNumber(), $poaModel->getReceivedDate(), $poaModel->getOriginalPaymentAmount(), $claim);
        $this->entityManager->persist($poa);

        foreach ($poaModel->getVerifications() as $verificationModel) {
            $verification = new VerificationEntity($verificationModel->getType(), $verificationModel->isPasses(), $poa);
            $poa->addVerification($verification);
            $this->entityManager->persist($verification);
        }

        $claim->addPoa($poa);

        $this->entityManager->flush();

        $claim = $this->getClaimEntity($claimId);

        /** @var ClaimModel $claimModel */
        $claimModel = $this->translateToDataModel($claim);
        return $claimModel;
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
}
